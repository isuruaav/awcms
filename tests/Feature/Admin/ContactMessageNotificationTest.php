<?php

use App\Livewire\Admin\ContactMessages\ContactMessageIndex;
use App\Livewire\Admin\ContactMessages\ContactMessageNavLink;
use App\Models\ContactMessage;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (['admin.access', 'dashboard.view', 'contacts.manage'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
});

function contactNotificationAdministrator(bool $mayManageContacts = true): User
{
    $user = User::factory()->create(['email_verified_at' => now()]);
    $permissions = ['admin.access', 'dashboard.view'];

    if ($mayManageContacts) {
        $permissions[] = 'contacts.manage';
    }

    $user->givePermissionTo($permissions);

    return $user;
}

function contactNotificationMessage(string $status = 'new'): ContactMessage
{
    return ContactMessage::query()->create([
        'name' => 'Test Visitor',
        'email' => 'visitor@example.com',
        'subject' => 'Course enquiry',
        'message' => 'Please send training information.',
        'status' => $status,
        'read_at' => $status === 'new' ? null : now(),
        'resolved_at' => $status === 'resolved' ? now() : null,
    ]);
}

it('keeps the contact link without a badge when there are no new messages', function (): void {
    Livewire::actingAs(contactNotificationAdministrator())
        ->test(ContactMessageNavLink::class)
        ->assertSet('unreadCount', 0)
        ->assertSee('Contact Messages')
        ->assertSee('wire:poll.15s.visible="refreshUnreadCount"', false)
        ->assertDontSee('New contact messages:');
});

it('counts only new messages in the sidebar badge', function (): void {
    contactNotificationMessage();
    contactNotificationMessage();
    contactNotificationMessage('read');
    contactNotificationMessage('resolved');

    Livewire::actingAs(contactNotificationAdministrator())
        ->test(ContactMessageNavLink::class)
        ->assertSet('unreadCount', 2)
        ->assertSee('New contact messages: 2')
        ->assertSee('2 new contact messages');
});

it('updates an existing sidebar badge after a public contact form submission', function (): void {
    $badge = Livewire::actingAs(contactNotificationAdministrator())
        ->test(ContactMessageNavLink::class)
        ->assertSet('unreadCount', 0);

    $this->post(route('contact.store'), [
        'name' => 'New Visitor',
        'email' => 'new-visitor@example.com',
        'subject' => 'New enquiry',
        'message' => 'Please send course information.',
        'website' => '',
    ])->assertSessionHasNoErrors();

    $badge->call('refreshUnreadCount')
        ->assertSet('unreadCount', 1)
        ->assertSee('New contact messages: 1');
});

it('updates the badge as soon as a message is opened', function (): void {
    $message = contactNotificationMessage();
    contactNotificationMessage();
    $administrator = contactNotificationAdministrator();

    $badge = Livewire::actingAs($administrator)
        ->test(ContactMessageNavLink::class)
        ->assertSet('unreadCount', 2);

    Livewire::test(ContactMessageIndex::class)
        ->call('openMessage', $message->id)
        ->assertHasNoErrors()
        ->assertDispatched('contact-messages-updated');

    $badge->dispatch('contact-messages-updated')
        ->assertSet('unreadCount', 1)
        ->assertSee('New contact messages: 1');

    expect($message->refresh()->status)->toBe('read');
});

it('removes the badge when the last new message is resolved', function (): void {
    $message = contactNotificationMessage();
    $badge = Livewire::actingAs(contactNotificationAdministrator())
        ->test(ContactMessageNavLink::class)
        ->assertSet('unreadCount', 1);

    Livewire::test(ContactMessageIndex::class)
        ->call('resolve', $message->id)
        ->assertHasNoErrors()
        ->assertDispatched('contact-messages-updated');

    $badge->dispatch('contact-messages-updated')
        ->assertSet('unreadCount', 0)
        ->assertDontSee('New contact messages:');
});

it('shows the badge in both desktop and mobile sidebars', function (): void {
    contactNotificationMessage();

    $response = $this->actingAs(contactNotificationAdministrator())
        ->get(route('admin.dashboard'))
        ->assertOk();

    expect(substr_count($response->getContent(), 'title="New contact messages: 1"'))->toBe(2);
});

it('does not expose the contact badge to administrators without contact permission', function (): void {
    contactNotificationMessage();
    $administrator = contactNotificationAdministrator(false);

    $this->actingAs($administrator)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('New contact messages:')
        ->assertDontSee('href="'.route('admin.contact-messages.index').'"', false);

    Livewire::actingAs($administrator)
        ->test(ContactMessageNavLink::class)
        ->assertForbidden();
});

it('caps the displayed badge while retaining the full count for accessibility', function (): void {
    foreach (range(1, 100) as $index) {
        contactNotificationMessage();
    }

    Livewire::actingAs(contactNotificationAdministrator())
        ->test(ContactMessageNavLink::class)
        ->assertSet('unreadCount', 100)
        ->assertSee('99+')
        ->assertSee('100 new contact messages');
});
