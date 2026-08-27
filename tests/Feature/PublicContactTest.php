<?php

use App\Models\ContactMessage;

test('guest can open contact page', function (): void {
    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSee('Get in touch');
});

test('guest can submit a contact message', function (): void {
    $this->post(route('contact.store'), [
        'name' => '<strong>Test Visitor</strong>',
        'email' => 'VISITOR@example.com',
        'phone' => '071 000 0000',
        'subject' => '<b>Website enquiry</b>',
        'message' => '<script>alert(1)</script>Hello from the public website.',
        'website' => '',
    ])->assertSessionHasNoErrors()->assertSessionHas('status');

    $message = ContactMessage::query()->firstOrFail();

    expect($message->name)->toBe('Test Visitor')
        ->and($message->email)->toBe('visitor@example.com')
        ->and($message->subject)->toBe('Website enquiry')
        ->and($message->message)->not->toContain('<script>');
});
