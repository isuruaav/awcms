<?php

// Compare code only. This script does not load Laravel, read .env, or write files.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this file using PHP CLI.');
}

$manifest = json_decode(<<<'MANIFEST'
{
  "reference": "2026-10-23",
  "directories": [
    "app",
    "bootstrap",
    "config",
    "database",
    "lang",
    "resources",
    "routes",
    "themes",
    "public/build",
    "public/themes"
  ],
  "files": {
    "app/Actions/Fortify/CreateNewUser.php": {
      "sha256": "caaaa98a8867c44ddaa45a1f58fd388f3b46f781d22cd9f8af67e292d3b82e60",
      "lf_sha256": "caaaa98a8867c44ddaa45a1f58fd388f3b46f781d22cd9f8af67e292d3b82e60"
    },
    "app/Actions/Fortify/ResetUserPassword.php": {
      "sha256": "8ec7f8a74a7f30b729bf77578d1b6a9add3c48643c92d584f7bb69e5b81c0996",
      "lf_sha256": "8ec7f8a74a7f30b729bf77578d1b6a9add3c48643c92d584f7bb69e5b81c0996"
    },
    "app/Concerns/PasswordValidationRules.php": {
      "sha256": "bc156c8d3556c45784ff6a319d02d7fb52ba2678ac4e4d6bf17d04861c4ebef7",
      "lf_sha256": "bc156c8d3556c45784ff6a319d02d7fb52ba2678ac4e4d6bf17d04861c4ebef7"
    },
    "app/Concerns/ProfileValidationRules.php": {
      "sha256": "274b9a825d140cc4a154f7ec816d368e1a9d7e8585158a70842b1d03415dae65",
      "lf_sha256": "274b9a825d140cc4a154f7ec816d368e1a9d7e8585158a70842b1d03415dae65"
    },
    "app/Console/Commands/BackfillPageRevisionsCommand.php": {
      "sha256": "81e5e82b4f65fdbb51f6fe957fcd2d61c2a179c9b768283d9e5c98eef59c2d7d",
      "lf_sha256": "81e5e82b4f65fdbb51f6fe957fcd2d61c2a179c9b768283d9e5c98eef59c2d7d"
    },
    "app/Console/Commands/CreateSuperAdministrator.php": {
      "sha256": "5e454f2a23b7f6d612a9bf23461198825e94085d560bbcb3a5aeb460cf279040",
      "lf_sha256": "5e454f2a23b7f6d612a9bf23461198825e94085d560bbcb3a5aeb460cf279040"
    },
    "app/Console/Commands/PublishThemeAssetsCommand.php": {
      "sha256": "184562b20171a03aced9fedbdebd6f31b22f1865d6e826713da5fb215f12d9c3",
      "lf_sha256": "184562b20171a03aced9fedbdebd6f31b22f1865d6e826713da5fb215f12d9c3"
    },
    "app/Enums/DocumentStatus.php": {
      "sha256": "e40a8e21569f59b59157c83df5b5048d467042998f409dfc7cbfa39dcd8d5adc",
      "lf_sha256": "e40a8e21569f59b59157c83df5b5048d467042998f409dfc7cbfa39dcd8d5adc"
    },
    "app/Enums/GalleryStatus.php": {
      "sha256": "d8310f5ef24c5716e0eef8625cd32c3918c092f311f8b0d6251b3eb38657c6c6",
      "lf_sha256": "d8310f5ef24c5716e0eef8625cd32c3918c092f311f8b0d6251b3eb38657c6c6"
    },
    "app/Enums/MediaSource.php": {
      "sha256": "5a2b6c93f0428578e098fa6d66097feb8de596ca67bf0c3138334ed1d4c1835d",
      "lf_sha256": "5a2b6c93f0428578e098fa6d66097feb8de596ca67bf0c3138334ed1d4c1835d"
    },
    "app/Enums/MediaType.php": {
      "sha256": "04128b85db6db23d89c78d383987d8970656ca9cb9c30fbf74fbf3427d4e0113",
      "lf_sha256": "04128b85db6db23d89c78d383987d8970656ca9cb9c30fbf74fbf3427d4e0113"
    },
    "app/Enums/MediaVariantPreset.php": {
      "sha256": "747322c59232e96cac54f8d0087175987822ac8d5d9d8d2c5e7695e192e7fc3f",
      "lf_sha256": "747322c59232e96cac54f8d0087175987822ac8d5d9d8d2c5e7695e192e7fc3f"
    },
    "app/Enums/MediaVisibility.php": {
      "sha256": "5b6d94138c0ae0719ff7d39ba16ca399d10b1ff33f7d9ed9d1094981f7f3f870",
      "lf_sha256": "5b6d94138c0ae0719ff7d39ba16ca399d10b1ff33f7d9ed9d1094981f7f3f870"
    },
    "app/Enums/MenuLocale.php": {
      "sha256": "97f3efe91b11bae208cf8daaffac1e814c9570e593314b76137a974f7c31da14",
      "lf_sha256": "97f3efe91b11bae208cf8daaffac1e814c9570e593314b76137a974f7c31da14"
    },
    "app/Enums/NewsEditorMode.php": {
      "sha256": "1987bb9c787a488fffe2baf76891329796aa3d80e78e91556d7a4ba14bb18a6a",
      "lf_sha256": "1987bb9c787a488fffe2baf76891329796aa3d80e78e91556d7a4ba14bb18a6a"
    },
    "app/Enums/NewsLocale.php": {
      "sha256": "e7eca17a23da611497f5cf68c31c1e360821035d193cdd86f8cbe8b750167b3c",
      "lf_sha256": "e7eca17a23da611497f5cf68c31c1e360821035d193cdd86f8cbe8b750167b3c"
    },
    "app/Enums/NewsStatus.php": {
      "sha256": "d05c3f88086738c39f428be53d4e0f58c0ceb303c9d3e1ff6a852740fc61bd4c",
      "lf_sha256": "d05c3f88086738c39f428be53d4e0f58c0ceb303c9d3e1ff6a852740fc61bd4c"
    },
    "app/Enums/PageBlockType.php": {
      "sha256": "7ecfb25ae3923db041432686d59d6aef3b1d9f3c347a09691ba1c46d3ce102e6",
      "lf_sha256": "7ecfb25ae3923db041432686d59d6aef3b1d9f3c347a09691ba1c46d3ce102e6"
    },
    "app/Enums/PageEditorMode.php": {
      "sha256": "4d28b724bbe057b242e24700c0b47a4b42791ce937045df984cdb01fa82fb23b",
      "lf_sha256": "4d28b724bbe057b242e24700c0b47a4b42791ce937045df984cdb01fa82fb23b"
    },
    "app/Enums/PageLocale.php": {
      "sha256": "92eb932d0f70229635251f59a745af5e0bd2a793965704fbb168e275ce8e945b",
      "lf_sha256": "92eb932d0f70229635251f59a745af5e0bd2a793965704fbb168e275ce8e945b"
    },
    "app/Enums/PageStatus.php": {
      "sha256": "faac490e7142f7b7fb192125c8d9ccd465d6d49a7f171be9ae69f5b4bc761495",
      "lf_sha256": "faac490e7142f7b7fb192125c8d9ccd465d6d49a7f171be9ae69f5b4bc761495"
    },
    "app/Enums/SchoolLeadershipAppointment.php": {
      "sha256": "f5ecd0049590cd656b02e183bbe599731688100b39dbef2b52f1cc0545a8ead4",
      "lf_sha256": "f5ecd0049590cd656b02e183bbe599731688100b39dbef2b52f1cc0545a8ead4"
    },
    "app/Enums/SchoolLeadershipRank.php": {
      "sha256": "026906f85797ef593191dd50ac81606edb1fd19d5c701217b04dba0e525c8190",
      "lf_sha256": "026906f85797ef593191dd50ac81606edb1fd19d5c701217b04dba0e525c8190"
    },
    "app/Enums/SchoolLeadershipRankGroup.php": {
      "sha256": "eb98273e36f3dabcbe20dc8d30b7df831d3cc161f5ac56ece9279e356948306b",
      "lf_sha256": "eb98273e36f3dabcbe20dc8d30b7df831d3cc161f5ac56ece9279e356948306b"
    },
    "app/Enums/ThemeLayoutLocale.php": {
      "sha256": "ed012562a6df6ef46e23133086fd69201746f2a0fde630b6e2a940f0c8e5bd54",
      "lf_sha256": "ed012562a6df6ef46e23133086fd69201746f2a0fde630b6e2a940f0c8e5bd54"
    },
    "app/Enums/ThemeLayoutRegion.php": {
      "sha256": "15bc50eed98a43d61910f86fabf0572725b06e5ed2684843cd42b564d0beb69d",
      "lf_sha256": "15bc50eed98a43d61910f86fabf0572725b06e5ed2684843cd42b564d0beb69d"
    },
    "app/Enums/ThemeLayoutStatus.php": {
      "sha256": "87805503e607a95f3302991277c77f50f20ee689584c0b722e57fb3aab749b3f",
      "lf_sha256": "87805503e607a95f3302991277c77f50f20ee689584c0b722e57fb3aab749b3f"
    },
    "app/Http/Controllers/Admin/DashboardController.php": {
      "sha256": "ed2282b3ba7b0d3e03dd0ca0a677b4900123650a6ffd605c414606f05be7f8fc",
      "lf_sha256": "ed2282b3ba7b0d3e03dd0ca0a677b4900123650a6ffd605c414606f05be7f8fc"
    },
    "app/Http/Controllers/Admin/NewsPreviewController.php": {
      "sha256": "11168c55c0ac762e2de1b6fe25b9df78c0eadaa54467a92e73997218b937f0af",
      "lf_sha256": "11168c55c0ac762e2de1b6fe25b9df78c0eadaa54467a92e73997218b937f0af"
    },
    "app/Http/Controllers/Admin/PagePreviewController.php": {
      "sha256": "d93e2d8a2a847a516f9f0987c64509128dc1ee38a982e0c6e16124cce32b1eaf",
      "lf_sha256": "d93e2d8a2a847a516f9f0987c64509128dc1ee38a982e0c6e16124cce32b1eaf"
    },
    "app/Http/Controllers/Controller.php": {
      "sha256": "25d1c1ef8e6cc8a376553faacfba2b07d9dfaee9bdbb84f14f77517580e9deb1",
      "lf_sha256": "25d1c1ef8e6cc8a376553faacfba2b07d9dfaee9bdbb84f14f77517580e9deb1"
    },
    "app/Http/Controllers/PastChiefInstructorController.php": {
      "sha256": "181cd26dc526f68851ba937f791d1b1f405ac81946c8de2bdf0bcd6f996ab8d1",
      "lf_sha256": "181cd26dc526f68851ba937f791d1b1f405ac81946c8de2bdf0bcd6f996ab8d1"
    },
    "app/Http/Controllers/PastCommandantController.php": {
      "sha256": "4cdf1f4d9528841b1152f57dfea139be59edf95aa6517ce8536826768c44dfd3",
      "lf_sha256": "4cdf1f4d9528841b1152f57dfea139be59edf95aa6517ce8536826768c44dfd3"
    },
    "app/Http/Controllers/PastSergeantMajorController.php": {
      "sha256": "cb542ff09daaff348c24d1a52cf3a82652a2690381bc66672a8bf321d9e05c93",
      "lf_sha256": "cb542ff09daaff348c24d1a52cf3a82652a2690381bc66672a8bf321d9e05c93"
    },
    "app/Http/Controllers/PublicContactController.php": {
      "sha256": "e88c319b6d9a99848db70f87be9ff89a7e899e856c17a21d28bf22985041c378",
      "lf_sha256": "e88c319b6d9a99848db70f87be9ff89a7e899e856c17a21d28bf22985041c378"
    },
    "app/Http/Controllers/PublicDocumentController.php": {
      "sha256": "10b197882faa13f3884ebd483f4433e9a91f8d21215cdaaabc09bd078ed47f76",
      "lf_sha256": "10b197882faa13f3884ebd483f4433e9a91f8d21215cdaaabc09bd078ed47f76"
    },
    "app/Http/Controllers/PublicGalleryController.php": {
      "sha256": "1d2d0348c190955d4c8ad4119d9a18109c547090a4efb7427e021701fb72f7d7",
      "lf_sha256": "1d2d0348c190955d4c8ad4119d9a18109c547090a4efb7427e021701fb72f7d7"
    },
    "app/Http/Controllers/PublicHomeController.php": {
      "sha256": "0c284f4087453f89d028d4689aec7f6d46f4e3d4b36666a2d98a54f415f639be",
      "lf_sha256": "0c284f4087453f89d028d4689aec7f6d46f4e3d4b36666a2d98a54f415f639be"
    },
    "app/Http/Controllers/PublicMediaFileController.php": {
      "sha256": "8d6637064281b3af4a6fd93725cdf16e80d6a80dfedce386a7bb4842bca0f497",
      "lf_sha256": "8d6637064281b3af4a6fd93725cdf16e80d6a80dfedce386a7bb4842bca0f497",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "8d6637064281b3af4a6fd93725cdf16e80d6a80dfedce386a7bb4842bca0f497",
          "lf_sha256": "8d6637064281b3af4a6fd93725cdf16e80d6a80dfedce386a7bb4842bca0f497"
        }
      ]
    },
    "app/Http/Controllers/PublicNewsController.php": {
      "sha256": "bf27fea01674fa32d5ce221dac6da1166d43ef1817c93c89853b5b4f554ff53a",
      "lf_sha256": "bf27fea01674fa32d5ce221dac6da1166d43ef1817c93c89853b5b4f554ff53a"
    },
    "app/Http/Controllers/PublicPageController.php": {
      "sha256": "47e52ca8403fbf2757d054531bd03cefd738c14fcdc4677fe5ec7180ea8b3b98",
      "lf_sha256": "47e52ca8403fbf2757d054531bd03cefd738c14fcdc4677fe5ec7180ea8b3b98"
    },
    "app/Http/Controllers/PublicRedirectController.php": {
      "sha256": "48abf1801136ef05ce5cd36825e49a5a49a85138145a23104e5e9f479be16ce3",
      "lf_sha256": "48abf1801136ef05ce5cd36825e49a5a49a85138145a23104e5e9f479be16ce3"
    },
    "app/Http/Controllers/PublicSchoolLeadershipController.php": {
      "sha256": "000f62dc7c5ceef087d5e1b7f0118b42357154ea7ad386fffb9882a536ae22c8",
      "lf_sha256": "000f62dc7c5ceef087d5e1b7f0118b42357154ea7ad386fffb9882a536ae22c8"
    },
    "app/Http/Middleware/EnsureSiteIsPublic.php": {
      "sha256": "7988b32e1e155734eb3c635aa84e535439be26760f73a594a7a1d9a1887141b2",
      "lf_sha256": "7988b32e1e155734eb3c635aa84e535439be26760f73a594a7a1d9a1887141b2"
    },
    "app/Http/Middleware/EnsureUserIsActive.php": {
      "sha256": "d51df46ec93f86a0f68bff92da78242fece58dbfc92416e3ef9d5100bdb0ef5e",
      "lf_sha256": "d51df46ec93f86a0f68bff92da78242fece58dbfc92416e3ef9d5100bdb0ef5e"
    },
    "app/Livewire/Actions/Logout.php": {
      "sha256": "d04551c0e48c5736b7c0e762a6a696ed3715ea2e8b00c1d97a57838eae6e86bc",
      "lf_sha256": "d04551c0e48c5736b7c0e762a6a696ed3715ea2e8b00c1d97a57838eae6e86bc"
    },
    "app/Livewire/Admin/AuditLogs/AuditLogIndex.php": {
      "sha256": "71194f57ec6390fab76f7f0a58c4c5f1440d8a5c3f07d3a62115e790b2c2fbd7",
      "lf_sha256": "71194f57ec6390fab76f7f0a58c4c5f1440d8a5c3f07d3a62115e790b2c2fbd7"
    },
    "app/Livewire/Admin/ContactMessages/ContactMessageIndex.php": {
      "sha256": "15646b297b7a13677e28905818b073656ed9d40a179216aca9f4a4f523c9abf9",
      "lf_sha256": "15646b297b7a13677e28905818b073656ed9d40a179216aca9f4a4f523c9abf9",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "15646b297b7a13677e28905818b073656ed9d40a179216aca9f4a4f523c9abf9",
          "lf_sha256": "15646b297b7a13677e28905818b073656ed9d40a179216aca9f4a4f523c9abf9"
        }
      ]
    },
    "app/Livewire/Admin/ContactMessages/ContactMessageNavLink.php": {
      "sha256": "0925bc4888596c5fc50e800b5ddf9f464c6238651ae9e77b97a716c03f2a1f10",
      "lf_sha256": "0925bc4888596c5fc50e800b5ddf9f464c6238651ae9e77b97a716c03f2a1f10",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "0925bc4888596c5fc50e800b5ddf9f464c6238651ae9e77b97a716c03f2a1f10",
          "lf_sha256": "0925bc4888596c5fc50e800b5ddf9f464c6238651ae9e77b97a716c03f2a1f10"
        }
      ]
    },
    "app/Livewire/Admin/Documents/DocumentCategoryIndex.php": {
      "sha256": "ab782d45d04f0dc441ce5f951fc28eb1f87d2b5aad070259182cb45dbcab5460",
      "lf_sha256": "ab782d45d04f0dc441ce5f951fc28eb1f87d2b5aad070259182cb45dbcab5460"
    },
    "app/Livewire/Admin/Documents/DocumentCreate.php": {
      "sha256": "592b73f26058294bdb81af672eaab14a3bccdfdef355e44d26af76d0a15147c4",
      "lf_sha256": "592b73f26058294bdb81af672eaab14a3bccdfdef355e44d26af76d0a15147c4"
    },
    "app/Livewire/Admin/Documents/DocumentEdit.php": {
      "sha256": "78ead2029ecc3126f9f9a75785cfeb9487d55623800278d15eeca1bf4617ffe5",
      "lf_sha256": "78ead2029ecc3126f9f9a75785cfeb9487d55623800278d15eeca1bf4617ffe5"
    },
    "app/Livewire/Admin/Documents/DocumentIndex.php": {
      "sha256": "046d67c942b1c10a5db3ecccc5bb1b4e629413cf35934fc23205d07b2442319f",
      "lf_sha256": "046d67c942b1c10a5db3ecccc5bb1b4e629413cf35934fc23205d07b2442319f"
    },
    "app/Livewire/Admin/Documents/DocumentUploads.php": {
      "sha256": "5d1be255d77e380781e9fa6e55a66806b238b374a7253459b77f7c96ffbafed5",
      "lf_sha256": "5d1be255d77e380781e9fa6e55a66806b238b374a7253459b77f7c96ffbafed5",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "5d1be255d77e380781e9fa6e55a66806b238b374a7253459b77f7c96ffbafed5",
          "lf_sha256": "5d1be255d77e380781e9fa6e55a66806b238b374a7253459b77f7c96ffbafed5"
        }
      ]
    },
    "app/Livewire/Admin/Galleries/GalleryCreate.php": {
      "sha256": "5bf423935f2a91f75527c3650be95f46172f7e52274d5600869fa3642bb1e87e",
      "lf_sha256": "5bf423935f2a91f75527c3650be95f46172f7e52274d5600869fa3642bb1e87e"
    },
    "app/Livewire/Admin/Galleries/GalleryEdit.php": {
      "sha256": "fb12fa37de8be693a461284b449a8a3739d9408c7f8cae4b8e6bc378c6969317",
      "lf_sha256": "fb12fa37de8be693a461284b449a8a3739d9408c7f8cae4b8e6bc378c6969317"
    },
    "app/Livewire/Admin/Galleries/GalleryIndex.php": {
      "sha256": "eae34a2a94f2ede6dd36442d38fc4f1d827d5fa5d6b546b760c688b518f8d170",
      "lf_sha256": "eae34a2a94f2ede6dd36442d38fc4f1d827d5fa5d6b546b760c688b518f8d170"
    },
    "app/Livewire/Admin/HeroSlides/HeroSlideIndex.php": {
      "sha256": "58016f34b5a20470534992203518f23ef6ac4bc9923c0b226398285924ca8781",
      "lf_sha256": "58016f34b5a20470534992203518f23ef6ac4bc9923c0b226398285924ca8781"
    },
    "app/Livewire/Admin/Media/MediaEdit.php": {
      "sha256": "f5bbe7466386ee8560dc9eea8fa2628fa528f55e447f06884bab20d9e8fb48a2",
      "lf_sha256": "f5bbe7466386ee8560dc9eea8fa2628fa528f55e447f06884bab20d9e8fb48a2",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "f5bbe7466386ee8560dc9eea8fa2628fa528f55e447f06884bab20d9e8fb48a2",
          "lf_sha256": "f5bbe7466386ee8560dc9eea8fa2628fa528f55e447f06884bab20d9e8fb48a2"
        }
      ]
    },
    "app/Livewire/Admin/Media/MediaIndex.php": {
      "sha256": "ef1531666b55e2294118c59cb4b854a59087ef598cd9ee1797e8fb3a8c368ea7",
      "lf_sha256": "ef1531666b55e2294118c59cb4b854a59087ef598cd9ee1797e8fb3a8c368ea7"
    },
    "app/Livewire/Admin/Media/MediaUpload.php": {
      "sha256": "542838354b8e8453a5474c81583a7a5a92d70da9ea0cd4022766d2197d64e458",
      "lf_sha256": "542838354b8e8453a5474c81583a7a5a92d70da9ea0cd4022766d2197d64e458",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "542838354b8e8453a5474c81583a7a5a92d70da9ea0cd4022766d2197d64e458",
          "lf_sha256": "542838354b8e8453a5474c81583a7a5a92d70da9ea0cd4022766d2197d64e458"
        }
      ]
    },
    "app/Livewire/Admin/News/NewsCategoryIndex.php": {
      "sha256": "a659d6949ad86ea102555b2e94597a103d7d8cf6587496983caea07d19280cc7",
      "lf_sha256": "a659d6949ad86ea102555b2e94597a103d7d8cf6587496983caea07d19280cc7"
    },
    "app/Livewire/Admin/News/NewsCreate.php": {
      "sha256": "65972e3c98b62da524d0a73f057c57a04abf83e019d6e0cd1f95b3f2c784fe6e",
      "lf_sha256": "65972e3c98b62da524d0a73f057c57a04abf83e019d6e0cd1f95b3f2c784fe6e"
    },
    "app/Livewire/Admin/News/NewsEdit.php": {
      "sha256": "f5e26ea1210a4d7a4ff35fc1613f600b73973011fa4c3dfc4d9366624c96a2f8",
      "lf_sha256": "f5e26ea1210a4d7a4ff35fc1613f600b73973011fa4c3dfc4d9366624c96a2f8"
    },
    "app/Livewire/Admin/News/NewsIndex.php": {
      "sha256": "9137004f424131e9fb06da8370d54dccd67a39cbe310d7d7a203a638b28d137f",
      "lf_sha256": "9137004f424131e9fb06da8370d54dccd67a39cbe310d7d7a203a638b28d137f",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "9137004f424131e9fb06da8370d54dccd67a39cbe310d7d7a203a638b28d137f",
          "lf_sha256": "9137004f424131e9fb06da8370d54dccd67a39cbe310d7d7a203a638b28d137f"
        },
        {
          "release": "2026-10-22-news-trash-fix",
          "sha256": "9137004f424131e9fb06da8370d54dccd67a39cbe310d7d7a203a638b28d137f",
          "lf_sha256": "9137004f424131e9fb06da8370d54dccd67a39cbe310d7d7a203a638b28d137f"
        }
      ]
    },
    "app/Livewire/Admin/News/NewsRevisionHistory.php": {
      "sha256": "502f0381d38c2d9feb67f57f84373ef494d911f25eab75a0873df8a0b0373b85",
      "lf_sha256": "502f0381d38c2d9feb67f57f84373ef494d911f25eab75a0873df8a0b0373b85"
    },
    "app/Livewire/Admin/Pages/PageCreate.php": {
      "sha256": "fa2694510a67f1f8ed68de6c1002d42fa9b9151d059e92c206b3e3c949b030f8",
      "lf_sha256": "fa2694510a67f1f8ed68de6c1002d42fa9b9151d059e92c206b3e3c949b030f8"
    },
    "app/Livewire/Admin/Pages/PageEdit.php": {
      "sha256": "6c7b169762c64f0c6f8dadf6a760835cc23a0c8d261b73b1961adcb1097f62be",
      "lf_sha256": "6c7b169762c64f0c6f8dadf6a760835cc23a0c8d261b73b1961adcb1097f62be"
    },
    "app/Livewire/Admin/Pages/PageIndex.php": {
      "sha256": "a6c5c22b6abb4670d5a4abc2aa63736d99b2ee62ac47a3a7f80bfb5887e265c3",
      "lf_sha256": "a6c5c22b6abb4670d5a4abc2aa63736d99b2ee62ac47a3a7f80bfb5887e265c3"
    },
    "app/Livewire/Admin/Pages/PageRevisionHistory.php": {
      "sha256": "2be8d06f28dca3d7d6fba7e9563e7f6b5d80a4c4e8825c3fe11adfb432f0b0dc",
      "lf_sha256": "2be8d06f28dca3d7d6fba7e9563e7f6b5d80a4c4e8825c3fe11adfb432f0b0dc"
    },
    "app/Livewire/Admin/Redirects/RedirectIndex.php": {
      "sha256": "c01c50b91619b391686c2f2daf982f4838966fa0f23393ebfe6cebc00247a4d1",
      "lf_sha256": "c01c50b91619b391686c2f2daf982f4838966fa0f23393ebfe6cebc00247a4d1"
    },
    "app/Livewire/Admin/Roles/RolePermissionIndex.php": {
      "sha256": "8c93c9e2e0f96b1e221ffe52f1273af788d296fcb02b98e4b0be4c0560f281e9",
      "lf_sha256": "8c93c9e2e0f96b1e221ffe52f1273af788d296fcb02b98e4b0be4c0560f281e9"
    },
    "app/Livewire/Admin/SchoolLeaders/SchoolLeaderIndex.php": {
      "sha256": "aa1bfa8c7869d4bb9c53329d3da0954f1c90c26b5a27aaa2c2815aef11cfc939",
      "lf_sha256": "aa1bfa8c7869d4bb9c53329d3da0954f1c90c26b5a27aaa2c2815aef11cfc939"
    },
    "app/Livewire/Admin/SchoolLeaders/SchoolLeaderProfiles.php": {
      "sha256": "6294ba1fb8ce16a22f2dcab915717a8ac1f5c99366c0d49f85e8ce57c3776240",
      "lf_sha256": "6294ba1fb8ce16a22f2dcab915717a8ac1f5c99366c0d49f85e8ce57c3776240"
    },
    "app/Livewire/Admin/SchoolLeaders/SchoolLeadershipPositionIndex.php": {
      "sha256": "97b8713b48be29b56cf18e3474ef622ffdb05942e0584e61b3a985c94224f7df",
      "lf_sha256": "97b8713b48be29b56cf18e3474ef622ffdb05942e0584e61b3a985c94224f7df"
    },
    "app/Livewire/Admin/Settings/SiteSettingsIndex.php": {
      "sha256": "09fdb0d2eb98c25346a78aca35fe29198769cfd24a93ce4e76a0aeedc5d05669",
      "lf_sha256": "09fdb0d2eb98c25346a78aca35fe29198769cfd24a93ce4e76a0aeedc5d05669",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "09fdb0d2eb98c25346a78aca35fe29198769cfd24a93ce4e76a0aeedc5d05669",
          "lf_sha256": "09fdb0d2eb98c25346a78aca35fe29198769cfd24a93ce4e76a0aeedc5d05669"
        }
      ]
    },
    "app/Livewire/Admin/ThemeLayouts/ThemeLayoutIndex.php": {
      "sha256": "64d739afc6641158e0e56c80414ed9bb77a0981c8ef5d7d4c92e6593e6f636d9",
      "lf_sha256": "64d739afc6641158e0e56c80414ed9bb77a0981c8ef5d7d4c92e6593e6f636d9"
    },
    "app/Livewire/Admin/Users/UserCreate.php": {
      "sha256": "0bf61c6e671e365144a4e032d22dc0827fb4852a1af17c8cdde4fab5056c62e6",
      "lf_sha256": "0bf61c6e671e365144a4e032d22dc0827fb4852a1af17c8cdde4fab5056c62e6"
    },
    "app/Livewire/Admin/Users/UserEdit.php": {
      "sha256": "843232ef2a94ac7b2611ac77f38af287c87cce9ebab02daf5ce66c7814759462",
      "lf_sha256": "843232ef2a94ac7b2611ac77f38af287c87cce9ebab02daf5ce66c7814759462"
    },
    "app/Livewire/Admin/Users/UserIndex.php": {
      "sha256": "5a78e0eb8a2a10c36d34fa9224e3c3fc6683f05d23c0c8f0a911662a2a88d671",
      "lf_sha256": "5a78e0eb8a2a10c36d34fa9224e3c3fc6683f05d23c0c8f0a911662a2a88d671"
    },
    "app/Livewire/Settings/Appearance.php": {
      "sha256": "274b1523aca61b7f0abb9c0c48fcb470c8c7885bb48313049c9b579b7abed44c",
      "lf_sha256": "274b1523aca61b7f0abb9c0c48fcb470c8c7885bb48313049c9b579b7abed44c"
    },
    "app/Livewire/Settings/DeleteUserForm.php": {
      "sha256": "7fede01839ab46ff3bb60a7feca76fa5d28fa7abb10fee94ad9f90cdcf8f8bdf",
      "lf_sha256": "7fede01839ab46ff3bb60a7feca76fa5d28fa7abb10fee94ad9f90cdcf8f8bdf"
    },
    "app/Livewire/Settings/Profile.php": {
      "sha256": "066bfeb6feec71ecb5859e3c7c58d840bf41068f5bc75a1a3dfd208e41608c70",
      "lf_sha256": "066bfeb6feec71ecb5859e3c7c58d840bf41068f5bc75a1a3dfd208e41608c70"
    },
    "app/Livewire/Settings/Security.php": {
      "sha256": "27336c11792f523ce9bc63fe3d45f56d67f2283a531ef8d52b26344c86fbcf5d",
      "lf_sha256": "27336c11792f523ce9bc63fe3d45f56d67f2283a531ef8d52b26344c86fbcf5d"
    },
    "app/Livewire/Settings/TwoFactor/RecoveryCodes.php": {
      "sha256": "fe6f356d812b8ecefea336905108a97a6880c8a1bfca29ba375a7beb5f70daa6",
      "lf_sha256": "fe6f356d812b8ecefea336905108a97a6880c8a1bfca29ba375a7beb5f70daa6"
    },
    "app/Models/AuditLog.php": {
      "sha256": "d10866bdd79d0eb7b11f43f1d170cc60a0c3984f64765175553e543e6c9b16a9",
      "lf_sha256": "d10866bdd79d0eb7b11f43f1d170cc60a0c3984f64765175553e543e6c9b16a9"
    },
    "app/Models/ContactMessage.php": {
      "sha256": "d1658bd96f44eb748a220b732d2ecec9afeabfd1aea2b165a2701252eca86cf9",
      "lf_sha256": "d1658bd96f44eb748a220b732d2ecec9afeabfd1aea2b165a2701252eca86cf9"
    },
    "app/Models/Document.php": {
      "sha256": "6c5135c5f4eb8e69630978d98bd81e2672f4e3cc6464534378da326131340312",
      "lf_sha256": "6c5135c5f4eb8e69630978d98bd81e2672f4e3cc6464534378da326131340312"
    },
    "app/Models/DocumentCategory.php": {
      "sha256": "d7bdd167239d3e2006b823a927b69541ba440f743bf669725679b159eb8b6586",
      "lf_sha256": "d7bdd167239d3e2006b823a927b69541ba440f743bf669725679b159eb8b6586"
    },
    "app/Models/DocumentVersion.php": {
      "sha256": "9637ae6d0f63bc255e3772eafd781cd1deb0dd7a1dd15c939eeac98cf4a49963",
      "lf_sha256": "9637ae6d0f63bc255e3772eafd781cd1deb0dd7a1dd15c939eeac98cf4a49963"
    },
    "app/Models/Gallery.php": {
      "sha256": "a4ee8bf1b5266b038ac511662adef84fb10e8eee83266f97399461037324f8af",
      "lf_sha256": "a4ee8bf1b5266b038ac511662adef84fb10e8eee83266f97399461037324f8af"
    },
    "app/Models/GalleryImage.php": {
      "sha256": "410b3f4274b661848f7c694ea5bef84a0c638de2f23359c83098d4bcc839b88f",
      "lf_sha256": "410b3f4274b661848f7c694ea5bef84a0c638de2f23359c83098d4bcc839b88f"
    },
    "app/Models/HeroSlide.php": {
      "sha256": "584d7139d749a8b132b014e72c153cfcd37f4474f1b22a84ada3ebf3fb1b96dd",
      "lf_sha256": "584d7139d749a8b132b014e72c153cfcd37f4474f1b22a84ada3ebf3fb1b96dd",
      "previous": [
        {
          "release": "2026-10-21-hero-button-link-fix",
          "sha256": "584d7139d749a8b132b014e72c153cfcd37f4474f1b22a84ada3ebf3fb1b96dd",
          "lf_sha256": "584d7139d749a8b132b014e72c153cfcd37f4474f1b22a84ada3ebf3fb1b96dd"
        }
      ]
    },
    "app/Models/HeroSlideTranslation.php": {
      "sha256": "aef892fc08da0eb8866c8aa7487fcb9e20f2e7a75706d83e526f93023f76ac5d",
      "lf_sha256": "aef892fc08da0eb8866c8aa7487fcb9e20f2e7a75706d83e526f93023f76ac5d"
    },
    "app/Models/MediaAsset.php": {
      "sha256": "b49f9551135684afd6a050ec3b0f3421651c7deb2500779b654acfd738b4a19b",
      "lf_sha256": "b49f9551135684afd6a050ec3b0f3421651c7deb2500779b654acfd738b4a19b",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "b49f9551135684afd6a050ec3b0f3421651c7deb2500779b654acfd738b4a19b",
          "lf_sha256": "b49f9551135684afd6a050ec3b0f3421651c7deb2500779b654acfd738b4a19b"
        }
      ]
    },
    "app/Models/MediaVariant.php": {
      "sha256": "2704fcb069173b1b39073d6d7bc27f164fe3eafdb88229c234419de8838dca98",
      "lf_sha256": "2704fcb069173b1b39073d6d7bc27f164fe3eafdb88229c234419de8838dca98"
    },
    "app/Models/Menu.php": {
      "sha256": "153f6a138a03859dea59aac001f9cb672c2ef7bd3a16ef48256f66be12c6d2ae",
      "lf_sha256": "153f6a138a03859dea59aac001f9cb672c2ef7bd3a16ef48256f66be12c6d2ae"
    },
    "app/Models/MenuItem.php": {
      "sha256": "70daf4beb945c942f310971f9f73f37e8c333e857ba222583c2aac73b8ae2c52",
      "lf_sha256": "70daf4beb945c942f310971f9f73f37e8c333e857ba222583c2aac73b8ae2c52"
    },
    "app/Models/MenuItemTranslation.php": {
      "sha256": "feb90d32a8dcb365221cdac825e60c4bfacdbf505c3510c19e50a1687f1594ad",
      "lf_sha256": "feb90d32a8dcb365221cdac825e60c4bfacdbf505c3510c19e50a1687f1594ad"
    },
    "app/Models/News.php": {
      "sha256": "81e0ac2cfc680da431f7b57a29092030feb827abe5edf8c106f659fd921534ab",
      "lf_sha256": "81e0ac2cfc680da431f7b57a29092030feb827abe5edf8c106f659fd921534ab"
    },
    "app/Models/NewsCategory.php": {
      "sha256": "717ae5abf9d3e9bee6e60fa48d7c02fd7c19a28c2f286feba4b277779f0298df",
      "lf_sha256": "717ae5abf9d3e9bee6e60fa48d7c02fd7c19a28c2f286feba4b277779f0298df"
    },
    "app/Models/NewsImage.php": {
      "sha256": "556b3f8109bd95ed0d97a31a85129ee0f68b4ea93b8a75040bf1817648279bf7",
      "lf_sha256": "556b3f8109bd95ed0d97a31a85129ee0f68b4ea93b8a75040bf1817648279bf7"
    },
    "app/Models/NewsRevision.php": {
      "sha256": "0e7100caa261d600775553b0522cb2821969aa9f36c6bec0698b730c46d052e0",
      "lf_sha256": "0e7100caa261d600775553b0522cb2821969aa9f36c6bec0698b730c46d052e0"
    },
    "app/Models/Page.php": {
      "sha256": "b76dd734b616e16eedd1322412613c7981694c1be30f6741dde3460a8077c501",
      "lf_sha256": "b76dd734b616e16eedd1322412613c7981694c1be30f6741dde3460a8077c501"
    },
    "app/Models/PageRevision.php": {
      "sha256": "ea9f20e9af9c88050eabb6602cd8fe6b0d91b3f1d7c0c109a03bd4fb692c5c48",
      "lf_sha256": "ea9f20e9af9c88050eabb6602cd8fe6b0d91b3f1d7c0c109a03bd4fb692c5c48"
    },
    "app/Models/PastChiefInstructor.php": {
      "sha256": "1c49114365a62462f1abd4a1a86dd87f6d97df0a0ca3b6e1f29f7d552a0f10e3",
      "lf_sha256": "1c49114365a62462f1abd4a1a86dd87f6d97df0a0ca3b6e1f29f7d552a0f10e3"
    },
    "app/Models/PastCommandant.php": {
      "sha256": "a9e77ef86b98744967981bce119df61a5fe6f5c7158177bc4049ca17b3e61527",
      "lf_sha256": "a9e77ef86b98744967981bce119df61a5fe6f5c7158177bc4049ca17b3e61527"
    },
    "app/Models/Redirect.php": {
      "sha256": "2dd449522b7ab3a49551f93a3a96ebaa4233e72e07b2050f2cf57169a5f064c0",
      "lf_sha256": "2dd449522b7ab3a49551f93a3a96ebaa4233e72e07b2050f2cf57169a5f064c0"
    },
    "app/Models/SchoolLeader.php": {
      "sha256": "0fba5804a858dfc62e109a9efce7d47b155624b29a6ed6fc71164c3035029bb2",
      "lf_sha256": "0fba5804a858dfc62e109a9efce7d47b155624b29a6ed6fc71164c3035029bb2"
    },
    "app/Models/SchoolLeadershipPosition.php": {
      "sha256": "74ec3e127916751c76242cfe55db85967ff8843aac46c523bb5d42d96ebcf557",
      "lf_sha256": "74ec3e127916751c76242cfe55db85967ff8843aac46c523bb5d42d96ebcf557"
    },
    "app/Models/SiteSetting.php": {
      "sha256": "b3b13e684574339c19ab683f585e01078a6d418b78cc0bc021b1eca65649cb3b",
      "lf_sha256": "b3b13e684574339c19ab683f585e01078a6d418b78cc0bc021b1eca65649cb3b"
    },
    "app/Models/SocialLink.php": {
      "sha256": "9851dad65f424c717126c677440a7b6f3d9154ac40fb53a8edc6aaf51cf1eb23",
      "lf_sha256": "9851dad65f424c717126c677440a7b6f3d9154ac40fb53a8edc6aaf51cf1eb23",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "9851dad65f424c717126c677440a7b6f3d9154ac40fb53a8edc6aaf51cf1eb23",
          "lf_sha256": "9851dad65f424c717126c677440a7b6f3d9154ac40fb53a8edc6aaf51cf1eb23"
        }
      ]
    },
    "app/Models/ThemeLayout.php": {
      "sha256": "bb80cf7d5da572d50db063826218c8a87c0ecc60378b08aa0097866b2eeeef21",
      "lf_sha256": "bb80cf7d5da572d50db063826218c8a87c0ecc60378b08aa0097866b2eeeef21"
    },
    "app/Models/ThemeLayoutRevision.php": {
      "sha256": "bf44a4b766e21e42409310f49b12550554bd9995aa6f68278978ff79d1a336cb",
      "lf_sha256": "bf44a4b766e21e42409310f49b12550554bd9995aa6f68278978ff79d1a336cb"
    },
    "app/Models/User.php": {
      "sha256": "44e3772532aa5e615a80f378b3d07ee180b2f59e0487756b431603877b62a1c0",
      "lf_sha256": "44e3772532aa5e615a80f378b3d07ee180b2f59e0487756b431603877b62a1c0"
    },
    "app/Providers/AppServiceProvider.php": {
      "sha256": "7bcd8f9e99d3ec447f2e1acfe1866cc659ceaac0208febd3f6d1520d76b61f5b",
      "lf_sha256": "7bcd8f9e99d3ec447f2e1acfe1866cc659ceaac0208febd3f6d1520d76b61f5b"
    },
    "app/Providers/FortifyServiceProvider.php": {
      "sha256": "0d1eb19f3a17521e213d41675e13e9b897ed1f42ea02bc6eb0e64fb6b0d58a62",
      "lf_sha256": "0d1eb19f3a17521e213d41675e13e9b897ed1f42ea02bc6eb0e64fb6b0d58a62"
    },
    "app/Providers/ThemeServiceProvider.php": {
      "sha256": "ec998873a7c7244fee3d9912e120e95a06040b18f78d319c7e89536bec721fa1",
      "lf_sha256": "ec998873a7c7244fee3d9912e120e95a06040b18f78d319c7e89536bec721fa1"
    },
    "app/Rules/ValidPageCss.php": {
      "sha256": "df58d815a0ff4ff119b279236c950694d8392c71a4c07ed1dfac34c1d21c0b4e",
      "lf_sha256": "df58d815a0ff4ff119b279236c950694d8392c71a4c07ed1dfac34c1d21c0b4e"
    },
    "app/Services/AuditLogger.php": {
      "sha256": "77af6acc5de63d5c95a9d5ad28c3dd0f5254928570eae07e833da15e39b5d375",
      "lf_sha256": "77af6acc5de63d5c95a9d5ad28c3dd0f5254928570eae07e833da15e39b5d375"
    },
    "app/Services/ContactMessageService.php": {
      "sha256": "e7ad18a7f151a21a9bc286c51c8c057cd8f5a0919b9c56b21e1f9c64340fe42f",
      "lf_sha256": "e7ad18a7f151a21a9bc286c51c8c057cd8f5a0919b9c56b21e1f9c64340fe42f"
    },
    "app/Services/ContentSanitizer.php": {
      "sha256": "abce48c471dd3ddde0a9b3c6900ddc51505cac4bdaec3b5fa17b90a7170e0437",
      "lf_sha256": "abce48c471dd3ddde0a9b3c6900ddc51505cac4bdaec3b5fa17b90a7170e0437"
    },
    "app/Services/DocumentCategoryService.php": {
      "sha256": "e8f2ae9b851c3e975269efb7c8552f3971290343a6b8f9a61758321619b26bf2",
      "lf_sha256": "e8f2ae9b851c3e975269efb7c8552f3971290343a6b8f9a61758321619b26bf2"
    },
    "app/Services/DocumentService.php": {
      "sha256": "b60242a4e562048e338b1f67ca6e340e1cec4adc06eadad3ed6a52a4b8539244",
      "lf_sha256": "b60242a4e562048e338b1f67ca6e340e1cec4adc06eadad3ed6a52a4b8539244"
    },
    "app/Services/DocumentUploadManager.php": {
      "sha256": "6dac5b336d11c93298142e746bd00d40ade1c10c807db4bedf44df7e805d5919",
      "lf_sha256": "6dac5b336d11c93298142e746bd00d40ade1c10c807db4bedf44df7e805d5919"
    },
    "app/Services/GalleryLocale.php": {
      "sha256": "24a495ac66fa437ac67736aaf763472fa127bc4cb5365afd7333aa1d966958f5",
      "lf_sha256": "24a495ac66fa437ac67736aaf763472fa127bc4cb5365afd7333aa1d966958f5"
    },
    "app/Services/GalleryService.php": {
      "sha256": "782935fff41bc9439210026d30acd7b0a5d4c435a504e42cbfb86d9059657e2b",
      "lf_sha256": "782935fff41bc9439210026d30acd7b0a5d4c435a504e42cbfb86d9059657e2b"
    },
    "app/Services/LeadershipProfileContent.php": {
      "sha256": "7a0dba99510c18d33ff4781812a7b1d5f93d0b98f27ef2ed0a486e738cbb92fc",
      "lf_sha256": "7a0dba99510c18d33ff4781812a7b1d5f93d0b98f27ef2ed0a486e738cbb92fc"
    },
    "app/Services/MediaDeletionService.php": {
      "sha256": "c80a351f6ce68102e857edcdce2424d2cc91f6ac2e3d00f5df53d127fbe587c2",
      "lf_sha256": "c80a351f6ce68102e857edcdce2424d2cc91f6ac2e3d00f5df53d127fbe587c2"
    },
    "app/Services/MediaImageVariantService.php": {
      "sha256": "22db7fb33cafeb0efd422186badf8d311e01be0331891a0ab966494977a214ce",
      "lf_sha256": "22db7fb33cafeb0efd422186badf8d311e01be0331891a0ab966494977a214ce"
    },
    "app/Services/MediaMetadataService.php": {
      "sha256": "a96e5c202a6e47d8cff6e469a8e9fac299e248df830e12d514fddc3365d91e9d",
      "lf_sha256": "a96e5c202a6e47d8cff6e469a8e9fac299e248df830e12d514fddc3365d91e9d"
    },
    "app/Services/MediaPublicFilename.php": {
      "sha256": "21cd7fe6dcf04a38cb6354f6ea9b74036fbee48dfeb1b74115f5b6e0d5c26e87",
      "lf_sha256": "21cd7fe6dcf04a38cb6354f6ea9b74036fbee48dfeb1b74115f5b6e0d5c26e87",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "21cd7fe6dcf04a38cb6354f6ea9b74036fbee48dfeb1b74115f5b6e0d5c26e87",
          "lf_sha256": "21cd7fe6dcf04a38cb6354f6ea9b74036fbee48dfeb1b74115f5b6e0d5c26e87"
        }
      ]
    },
    "app/Services/MediaReplacementService.php": {
      "sha256": "aed4121f08cae3099d475b5b1ff69c251929adf9e4c32178e64d156a0c7aa2f2",
      "lf_sha256": "aed4121f08cae3099d475b5b1ff69c251929adf9e4c32178e64d156a0c7aa2f2"
    },
    "app/Services/MediaUploadSecurity.php": {
      "sha256": "51fa1793675e225e5a8c54f968496b91e672032690e49bbe0e930325587cbca5",
      "lf_sha256": "51fa1793675e225e5a8c54f968496b91e672032690e49bbe0e930325587cbca5"
    },
    "app/Services/MediaUploadService.php": {
      "sha256": "f0bebb2db142266472fbae8a204aef8001cf16923c153f9e40d526b420499e21",
      "lf_sha256": "f0bebb2db142266472fbae8a204aef8001cf16923c153f9e40d526b420499e21"
    },
    "app/Services/MediaUrlService.php": {
      "sha256": "994ab3fe9d05b7e61744f887f4cd56c0f1980eb1f287716bd7d00c2de4b1ba5a",
      "lf_sha256": "994ab3fe9d05b7e61744f887f4cd56c0f1980eb1f287716bd7d00c2de4b1ba5a",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "994ab3fe9d05b7e61744f887f4cd56c0f1980eb1f287716bd7d00c2de4b1ba5a",
          "lf_sha256": "994ab3fe9d05b7e61744f887f4cd56c0f1980eb1f287716bd7d00c2de4b1ba5a"
        }
      ]
    },
    "app/Services/MenuService.php": {
      "sha256": "a3a3ddfde7174c24b9f5f19e60ed7d90d89ccd8455af6fc86ef35ad07025ef31",
      "lf_sha256": "a3a3ddfde7174c24b9f5f19e60ed7d90d89ccd8455af6fc86ef35ad07025ef31"
    },
    "app/Services/NewsArticleService.php": {
      "sha256": "3e2452b67725da1fbf07244f0040f759196722995a4b9b20e97229ad7bc98c18",
      "lf_sha256": "3e2452b67725da1fbf07244f0040f759196722995a4b9b20e97229ad7bc98c18"
    },
    "app/Services/NewsCategoryService.php": {
      "sha256": "5fea0e7c34d9e58614f035959ce437a415a37634c8101ef81fc3c8ebc0f1c702",
      "lf_sha256": "5fea0e7c34d9e58614f035959ce437a415a37634c8101ef81fc3c8ebc0f1c702"
    },
    "app/Services/NewsDeletionService.php": {
      "sha256": "437e26ad09b34a5b63e128a6016bff4f0ba06070d1c264d000707ddf5213533f",
      "lf_sha256": "437e26ad09b34a5b63e128a6016bff4f0ba06070d1c264d000707ddf5213533f",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "437e26ad09b34a5b63e128a6016bff4f0ba06070d1c264d000707ddf5213533f",
          "lf_sha256": "437e26ad09b34a5b63e128a6016bff4f0ba06070d1c264d000707ddf5213533f"
        },
        {
          "release": "2026-10-22-news-trash-fix",
          "sha256": "437e26ad09b34a5b63e128a6016bff4f0ba06070d1c264d000707ddf5213533f",
          "lf_sha256": "437e26ad09b34a5b63e128a6016bff4f0ba06070d1c264d000707ddf5213533f"
        }
      ]
    },
    "app/Services/NewsImageService.php": {
      "sha256": "5f3252dd2df2583cb9d64a8db856673a18758cc949f02f78bf59aabad8a49e75",
      "lf_sha256": "5f3252dd2df2583cb9d64a8db856673a18758cc949f02f78bf59aabad8a49e75"
    },
    "app/Services/NewsRevisionService.php": {
      "sha256": "aaaec06daf61e36b93ceead4e3831dd42f7b952f522d278645c1497d6ded40fc",
      "lf_sha256": "aaaec06daf61e36b93ceead4e3831dd42f7b952f522d278645c1497d6ded40fc"
    },
    "app/Services/NewsWorkflowService.php": {
      "sha256": "9f92a70ddd4b0e057e3d90d3b0e7874753860eb426b5c7844326ac16a3f711f0",
      "lf_sha256": "9f92a70ddd4b0e057e3d90d3b0e7874753860eb426b5c7844326ac16a3f711f0"
    },
    "app/Services/PageBlockRenderer.php": {
      "sha256": "046b808b1999170a502d5938a243c4c5dbfd0af36b8714628e1cf7f6786aec78",
      "lf_sha256": "046b808b1999170a502d5938a243c4c5dbfd0af36b8714628e1cf7f6786aec78"
    },
    "app/Services/PageBlockSanitizer.php": {
      "sha256": "f3f1a847fed50c566bf1e4953ce3b26243bdc95504c4e39d92e75eb248d4d2bc",
      "lf_sha256": "f3f1a847fed50c566bf1e4953ce3b26243bdc95504c4e39d92e75eb248d4d2bc"
    },
    "app/Services/PageCss.php": {
      "sha256": "09547208b2b3b9178412dcefc2b63a2b99d73eda1a7f8a7762ae5fb8b5eba0d5",
      "lf_sha256": "09547208b2b3b9178412dcefc2b63a2b99d73eda1a7f8a7762ae5fb8b5eba0d5"
    },
    "app/Services/PageDeletionService.php": {
      "sha256": "14c2c557f55ecabe9920890298ff887400d774cecb39c80d539e74ea35f5913c",
      "lf_sha256": "14c2c557f55ecabe9920890298ff887400d774cecb39c80d539e74ea35f5913c"
    },
    "app/Services/PageHtmlSanitizer.php": {
      "sha256": "bf21de01ff0ec4ae00c2dfc9d3504e64f32939429aaf195f4c3012af0bebb199",
      "lf_sha256": "bf21de01ff0ec4ae00c2dfc9d3504e64f32939429aaf195f4c3012af0bebb199"
    },
    "app/Services/PageRevisionService.php": {
      "sha256": "26e1f07d075709fdc5d622a6e703946ecb1e39b2da202b1f9a23dbb130de8511",
      "lf_sha256": "26e1f07d075709fdc5d622a6e703946ecb1e39b2da202b1f9a23dbb130de8511"
    },
    "app/Services/PageWorkflowService.php": {
      "sha256": "55262862e6f2df95d579a329d8f98fc7ffb15da47a76de38160e4e9886268a59",
      "lf_sha256": "55262862e6f2df95d579a329d8f98fc7ffb15da47a76de38160e4e9886268a59"
    },
    "app/Services/PublicGalleryFeed.php": {
      "sha256": "7d36576197884c983794bbc4d3b0d6394cbeeb5ba24ed70ff98ffb99c6ec824d",
      "lf_sha256": "7d36576197884c983794bbc4d3b0d6394cbeeb5ba24ed70ff98ffb99c6ec824d"
    },
    "app/Services/RedirectService.php": {
      "sha256": "ffb8cd3a5867efc5207203a768742cb9d3aece16f616f5307c103d55f8bffa3f",
      "lf_sha256": "ffb8cd3a5867efc5207203a768742cb9d3aece16f616f5307c103d55f8bffa3f"
    },
    "app/Services/SiteSettingService.php": {
      "sha256": "89f0deb564528ab629b635935850eca7783567f2d75309a160825b8a9e61a603",
      "lf_sha256": "89f0deb564528ab629b635935850eca7783567f2d75309a160825b8a9e61a603"
    },
    "app/Services/ThemeAssetPublisher.php": {
      "sha256": "3f2fe2b26cc85ff20a2190ca7152ea17760f8c5a8113cd27ce9b92cf5ae0adac",
      "lf_sha256": "3f2fe2b26cc85ff20a2190ca7152ea17760f8c5a8113cd27ce9b92cf5ae0adac"
    },
    "app/Services/ThemeLayoutRenderer.php": {
      "sha256": "c2573f85c03d37b910e92bee438dbb54383bf0fbc5924e86400de6f70afd5413",
      "lf_sha256": "c2573f85c03d37b910e92bee438dbb54383bf0fbc5924e86400de6f70afd5413",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "b2b7e3a00a4f9336da61a925ed0493270bde44d698b722c756af888ae3b0c6b5",
          "lf_sha256": "b2b7e3a00a4f9336da61a925ed0493270bde44d698b722c756af888ae3b0c6b5"
        }
      ]
    },
    "app/Services/ThemeLayoutSanitizer.php": {
      "sha256": "cd0ca9bdc9f073a15e2b5525c894126861c879ad5914f768dd8d3242099b8836",
      "lf_sha256": "cd0ca9bdc9f073a15e2b5525c894126861c879ad5914f768dd8d3242099b8836",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "cd0ca9bdc9f073a15e2b5525c894126861c879ad5914f768dd8d3242099b8836",
          "lf_sha256": "cd0ca9bdc9f073a15e2b5525c894126861c879ad5914f768dd8d3242099b8836"
        }
      ]
    },
    "app/Services/ThemeLayoutService.php": {
      "sha256": "8276055f5e43d32a972ad467be2f4ab08c191cee93225713f769e3172b73b33d",
      "lf_sha256": "8276055f5e43d32a972ad467be2f4ab08c191cee93225713f769e3172b73b33d"
    },
    "app/Services/ThemeManager.php": {
      "sha256": "1acfa56a711d6c0c025cfd6df8489ad478cc02f0c356ecae4302780a173ed8da",
      "lf_sha256": "1acfa56a711d6c0c025cfd6df8489ad478cc02f0c356ecae4302780a173ed8da"
    },
    "app/Services/ThemeViewResolver.php": {
      "sha256": "7a4e01006634332a01c799105cfb41f8618c95df98d0990193626e204abc6786",
      "lf_sha256": "7a4e01006634332a01c799105cfb41f8618c95df98d0990193626e204abc6786"
    },
    "app/Support/PageBlockFactory.php": {
      "sha256": "deabb5659f7395c51673fd713345ee6eea17262ed1724ab8f1358837db997afa",
      "lf_sha256": "deabb5659f7395c51673fd713345ee6eea17262ed1724ab8f1358837db997afa"
    },
    "app/Support/PageMeta.php": {
      "sha256": "1f34c0f9a78f57d67663a7317afe7a7a9471f09e7d06407605dfb3efa2db83f2",
      "lf_sha256": "1f34c0f9a78f57d67663a7317afe7a7a9471f09e7d06407605dfb3efa2db83f2"
    },
    "app/Support/PageSeo.php": {
      "sha256": "04fc7af23d5019723d5575eee84e3b934662b0773ac4bc26dc4403a5cfe4fd33",
      "lf_sha256": "04fc7af23d5019723d5575eee84e3b934662b0773ac4bc26dc4403a5cfe4fd33"
    },
    "app/Support/PageSlugger.php": {
      "sha256": "aabc872be7e97b884beaab7fb6bd61387e239fe3209195039184fb55e1d90a1f",
      "lf_sha256": "aabc872be7e97b884beaab7fb6bd61387e239fe3209195039184fb55e1d90a1f"
    },
    "app/Support/UserManagementRules.php": {
      "sha256": "1a3c8a6f6bae2e936a1e5bd90d1bef98a7d5078969de41c73636648c65d2ec96",
      "lf_sha256": "1a3c8a6f6bae2e936a1e5bd90d1bef98a7d5078969de41c73636648c65d2ec96"
    },
    "artisan": {
      "sha256": "f91a517c0c5d3a904c3f390933964a5e08ec1fc68146882730daac4aca4ffcec",
      "lf_sha256": "f91a517c0c5d3a904c3f390933964a5e08ec1fc68146882730daac4aca4ffcec"
    },
    "bootstrap/app.php": {
      "sha256": "9fd633aace0e4c79e502a44900096092c6c7f31ecfd35401ea8b879e169f4d24",
      "lf_sha256": "9fd633aace0e4c79e502a44900096092c6c7f31ecfd35401ea8b879e169f4d24"
    },
    "bootstrap/providers.php": {
      "sha256": "eb67bea31d3b38e0aa351404f386b4cb5cc68b09f37bf63eef24d023130d2167",
      "lf_sha256": "eb67bea31d3b38e0aa351404f386b4cb5cc68b09f37bf63eef24d023130d2167"
    },
    "composer.json": {
      "sha256": "a8b7cea6a1a54fd341430e2a5ac349019f76f1768055f6332187ddf5077f6992",
      "lf_sha256": "a8b7cea6a1a54fd341430e2a5ac349019f76f1768055f6332187ddf5077f6992"
    },
    "composer.lock": {
      "sha256": "2c5493f29d36ed5ea71fb684f2ba225915ca47c6a056705ae5b8784f3f0c6f86"
    },
    "config/app.php": {
      "sha256": "59481af5652fd9bd28d893bb5cf6539f4a6a91cef064efe030faa7b0ad651227",
      "lf_sha256": "59481af5652fd9bd28d893bb5cf6539f4a6a91cef064efe030faa7b0ad651227"
    },
    "config/auth.php": {
      "sha256": "c7e204e9785c9f596d66fb884b493f658f2327f157646bdb4088efd6b3a7773f",
      "lf_sha256": "c7e204e9785c9f596d66fb884b493f658f2327f157646bdb4088efd6b3a7773f"
    },
    "config/awcms.php": {
      "sha256": "5b9b1ea13ac1a1c6f84b853b186ed273ce43ce2aa0ae45156475c12f79aa8ad1",
      "lf_sha256": "5b9b1ea13ac1a1c6f84b853b186ed273ce43ce2aa0ae45156475c12f79aa8ad1"
    },
    "config/cache.php": {
      "sha256": "ee4ad2bba1edfcff52e9599f37371f2370d0b167cc226ce72d46cdf6edf2dc55",
      "lf_sha256": "ee4ad2bba1edfcff52e9599f37371f2370d0b167cc226ce72d46cdf6edf2dc55"
    },
    "config/database.php": {
      "sha256": "6ddb4706767001f64db9b47a78ce9c0989862387cd9d24e843a5d6157fad86dd",
      "lf_sha256": "6ddb4706767001f64db9b47a78ce9c0989862387cd9d24e843a5d6157fad86dd"
    },
    "config/filesystems.php": {
      "sha256": "7e51d9aed3ee11bd8e15b6cdda38d02eebb378166cbe7c9f23980e1e6e3964b5",
      "lf_sha256": "7e51d9aed3ee11bd8e15b6cdda38d02eebb378166cbe7c9f23980e1e6e3964b5"
    },
    "config/fortify.php": {
      "sha256": "d390e0ab22e4ebf992966ab4ded6cac2f30b75fc4d929e5a7a3d141aa3847356",
      "lf_sha256": "d390e0ab22e4ebf992966ab4ded6cac2f30b75fc4d929e5a7a3d141aa3847356"
    },
    "config/images.php": {
      "sha256": "749d0426dac0522614783701549ca1676db7fadaca307868907aca4fcf4cadf4",
      "lf_sha256": "749d0426dac0522614783701549ca1676db7fadaca307868907aca4fcf4cadf4"
    },
    "config/livewire.php": {
      "sha256": "0680a9b9f9b7be4b78982f5d02441657b4bfd53091f6e4de9234b2877eb626ea",
      "lf_sha256": "0680a9b9f9b7be4b78982f5d02441657b4bfd53091f6e4de9234b2877eb626ea"
    },
    "config/logging.php": {
      "sha256": "299ec1ba5b5a803c8e20f666ae7628e442e856cc2ff7ac9e2adb17b97e8ad63b",
      "lf_sha256": "299ec1ba5b5a803c8e20f666ae7628e442e856cc2ff7ac9e2adb17b97e8ad63b"
    },
    "config/mail.php": {
      "sha256": "c43ff49a31c5f32ce21eb7159f2a4b3cd2f4074eaed5838d265646dbfedd3474",
      "lf_sha256": "c43ff49a31c5f32ce21eb7159f2a4b3cd2f4074eaed5838d265646dbfedd3474"
    },
    "config/media.php": {
      "sha256": "b2355696f4b8200674095357096a67a21d4d39a48be911d2b25710d7f76312ff",
      "lf_sha256": "b2355696f4b8200674095357096a67a21d4d39a48be911d2b25710d7f76312ff"
    },
    "config/permission.php": {
      "sha256": "bb8d175e181841563fa6bb670fe075725967b25cffa4a1294395e046f059f4b1",
      "lf_sha256": "bb8d175e181841563fa6bb670fe075725967b25cffa4a1294395e046f059f4b1"
    },
    "config/queue.php": {
      "sha256": "6101774da7c8b79af46d0028f2b8b31a484c3f7eda10b76e5b27b6093ad364a4",
      "lf_sha256": "6101774da7c8b79af46d0028f2b8b31a484c3f7eda10b76e5b27b6093ad364a4"
    },
    "config/services.php": {
      "sha256": "b7ee786ea1adbc92db9f229f479431c94503527c47051da3862eb0e4d7db70aa",
      "lf_sha256": "b7ee786ea1adbc92db9f229f479431c94503527c47051da3862eb0e4d7db70aa"
    },
    "config/session.php": {
      "sha256": "272de4fd3ad8b806f5726a34d2a4ad11cbdbd4184fcad17143280ee2a8d4c7d6",
      "lf_sha256": "272de4fd3ad8b806f5726a34d2a4ad11cbdbd4184fcad17143280ee2a8d4c7d6"
    },
    "database/database.sqlite": {
      "sha256": "571e879a13df6cae106d60c748bb65db2f6c303083192d9d4f142e308dd81890"
    },
    "database/factories/GalleryFactory.php": {
      "sha256": "cd7f34f43a40f364ea2e18c22e781a69a862bd52fb3bb139baa232a34ab08f48",
      "lf_sha256": "cd7f34f43a40f364ea2e18c22e781a69a862bd52fb3bb139baa232a34ab08f48"
    },
    "database/factories/GalleryImageFactory.php": {
      "sha256": "b6aa07b21e4364f2680f179858262e5f6aed0abf247914d78e84b0eeb1ae2482",
      "lf_sha256": "b6aa07b21e4364f2680f179858262e5f6aed0abf247914d78e84b0eeb1ae2482"
    },
    "database/factories/MediaAssetFactory.php": {
      "sha256": "527e636488e21c976cfae12a35ac516f0ed561ba59db22f75e762095952a7961",
      "lf_sha256": "527e636488e21c976cfae12a35ac516f0ed561ba59db22f75e762095952a7961"
    },
    "database/factories/NewsCategoryFactory.php": {
      "sha256": "f5cc5d861199203fd48bebfc64a57254bc0c157537a9fc6f0fbaf99238bf5a40",
      "lf_sha256": "f5cc5d861199203fd48bebfc64a57254bc0c157537a9fc6f0fbaf99238bf5a40"
    },
    "database/factories/NewsFactory.php": {
      "sha256": "36355451dc10009000c900cd838ba3268ca2f89296246e2e772a7f94cf7af34d",
      "lf_sha256": "36355451dc10009000c900cd838ba3268ca2f89296246e2e772a7f94cf7af34d"
    },
    "database/factories/NewsRevisionFactory.php": {
      "sha256": "11fb8521fd3d28f6ba9077d33cf0ab2c7cfbfb92a7aae39aa370b3e20ea7868f",
      "lf_sha256": "11fb8521fd3d28f6ba9077d33cf0ab2c7cfbfb92a7aae39aa370b3e20ea7868f"
    },
    "database/factories/PageFactory.php": {
      "sha256": "dd952da924691126ea3b944b3466254d3d54eb705dcb3b5abec54374ea7cde3b",
      "lf_sha256": "dd952da924691126ea3b944b3466254d3d54eb705dcb3b5abec54374ea7cde3b"
    },
    "database/factories/UserFactory.php": {
      "sha256": "d8659afbb893a1658887ece9307777a178470c1f7e2302b65ec01b8664c20e35",
      "lf_sha256": "d8659afbb893a1658887ece9307777a178470c1f7e2302b65ec01b8664c20e35"
    },
    "database/migrations/0001_01_01_000000_create_users_table.php": {
      "sha256": "10d79883bc08ce46510f9ad8b1830b4d06d586dc56ae2c8bac3b3e491f69a90f",
      "lf_sha256": "10d79883bc08ce46510f9ad8b1830b4d06d586dc56ae2c8bac3b3e491f69a90f"
    },
    "database/migrations/0001_01_01_000001_create_cache_table.php": {
      "sha256": "fbb4665e5a977e71df4e74ec75b3c06ca4f17ad0de7138fe9786639189978e77",
      "lf_sha256": "fbb4665e5a977e71df4e74ec75b3c06ca4f17ad0de7138fe9786639189978e77"
    },
    "database/migrations/0001_01_01_000002_create_jobs_table.php": {
      "sha256": "df4910687180313a14a3f96a8bddd808ae145ed1b107cd772caf865c0dc2b746",
      "lf_sha256": "df4910687180313a14a3f96a8bddd808ae145ed1b107cd772caf865c0dc2b746"
    },
    "database/migrations/2024_01_01_000000_create_passkeys_table.php": {
      "sha256": "3c866281584abdc808b6b6fe1d28aae55d997d3f97c5dd2d07ae106ff58531fc",
      "lf_sha256": "3c866281584abdc808b6b6fe1d28aae55d997d3f97c5dd2d07ae106ff58531fc"
    },
    "database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php": {
      "sha256": "79f22594944d826286ad1dafb13217967781e104dbb021a1c85fbdf9270db4a5",
      "lf_sha256": "79f22594944d826286ad1dafb13217967781e104dbb021a1c85fbdf9270db4a5"
    },
    "database/migrations/2026_07_29_210841_create_permission_tables.php": {
      "sha256": "bd097ba10f37a131a9a8885ed4723387521a2f222f636b37b911e7b9ed34eec2",
      "lf_sha256": "bd097ba10f37a131a9a8885ed4723387521a2f222f636b37b911e7b9ed34eec2"
    },
    "database/migrations/2026_07_30_072444_add_management_fields_to_users_table.php": {
      "sha256": "e46931b84de1695582cfb915905c6191de4d434d71db3dbffdfaf2cabaa3d5b2",
      "lf_sha256": "e46931b84de1695582cfb915905c6191de4d434d71db3dbffdfaf2cabaa3d5b2"
    },
    "database/migrations/2026_07_30_174658_create_audit_logs_table.php": {
      "sha256": "b87b0b771e3ed37687f5b1442c786bc39aa73e8d2e0e95cc22a3c63fa60bd3c3",
      "lf_sha256": "b87b0b771e3ed37687f5b1442c786bc39aa73e8d2e0e95cc22a3c63fa60bd3c3"
    },
    "database/migrations/2026_07_30_203704_create_pages_table.php": {
      "sha256": "afbe2dd5bd48b7b3be47e101275bd70ec4680495efdddf6209ec4cc31fa340d3",
      "lf_sha256": "afbe2dd5bd48b7b3be47e101275bd70ec4680495efdddf6209ec4cc31fa340d3"
    },
    "database/migrations/2026_07_31_145549_create_page_revisions_table.php": {
      "sha256": "19e7b9f853e8ef0c7eb8f16d3c796b481c202d0ad8ac2206b563e0dc7fa22951",
      "lf_sha256": "19e7b9f853e8ef0c7eb8f16d3c796b481c202d0ad8ac2206b563e0dc7fa22951"
    },
    "database/migrations/2026_08_17_191344_add_seo_fields_to_pages_table.php": {
      "sha256": "6edcafc342ca84bdb1b1816c5aab71e5448c5993fbb536a7afca75676b5d2a6d",
      "lf_sha256": "6edcafc342ca84bdb1b1816c5aab71e5448c5993fbb536a7afca75676b5d2a6d"
    },
    "database/migrations/2026_08_17_192013_add_seo_fields_to_page_revisions_table.php": {
      "sha256": "f683da13dadfceee9d912cd6d80ac01dacde33a43ad2ada29217a308d42a100e",
      "lf_sha256": "f683da13dadfceee9d912cd6d80ac01dacde33a43ad2ada29217a308d42a100e"
    },
    "database/migrations/2026_08_18_140000_create_media_assets_table.php": {
      "sha256": "338a0e5a55ddad485ce652997dfc76e02a6793b05520412133746ece819a5b19",
      "lf_sha256": "338a0e5a55ddad485ce652997dfc76e02a6793b05520412133746ece819a5b19"
    },
    "database/migrations/2026_08_19_091418_create_media_variants_table.php": {
      "sha256": "905e8394bbed1fb37ace044e18489dbe5a8543df5b445f41bc81675e27a4b615",
      "lf_sha256": "905e8394bbed1fb37ace044e18489dbe5a8543df5b445f41bc81675e27a4b615"
    },
    "database/migrations/2026_08_19_181912_create_news_categories_table.php": {
      "sha256": "e9721da1f03d3c445d57590484e4c12a9ee77ea77355c49452db963fa3b01652",
      "lf_sha256": "e9721da1f03d3c445d57590484e4c12a9ee77ea77355c49452db963fa3b01652"
    },
    "database/migrations/2026_08_19_181914_create_news_table.php": {
      "sha256": "2df4cbb605c5e179dfc116b1f37365f19d6e9341cec209166c59c7c9fd5dedc8",
      "lf_sha256": "2df4cbb605c5e179dfc116b1f37365f19d6e9341cec209166c59c7c9fd5dedc8"
    },
    "database/migrations/2026_08_19_204327_create_news_revisions_table.php": {
      "sha256": "bc9c526b3f8c90367afcda5f6fcb060aaa92edbb06bf9ee9add53db346972e43",
      "lf_sha256": "bc9c526b3f8c90367afcda5f6fcb060aaa92edbb06bf9ee9add53db346972e43"
    },
    "database/migrations/2026_08_24_192347_add_review_workflow_fields_to_news_table.php": {
      "sha256": "5a4748ebf9c1ad53903c7e11c51345f695dac2e2abd68196f7cf25791f945525",
      "lf_sha256": "5a4748ebf9c1ad53903c7e11c51345f695dac2e2abd68196f7cf25791f945525"
    },
    "database/migrations/2026_08_24_203114_create_galleries_table.php": {
      "sha256": "d3ed6c66df27664169cb46c7ec203a72f6d3bc7e0698633127fcfd4342f307f6",
      "lf_sha256": "d3ed6c66df27664169cb46c7ec203a72f6d3bc7e0698633127fcfd4342f307f6"
    },
    "database/migrations/2026_08_24_203115_create_gallery_images_table.php": {
      "sha256": "b71a0dc54e55126d2756b3aca5f41f5fa72e9fe93a13454767205f2ceec77231",
      "lf_sha256": "b71a0dc54e55126d2756b3aca5f41f5fa72e9fe93a13454767205f2ceec77231"
    },
    "database/migrations/2026_08_25_163253_create_document_categories_table.php": {
      "sha256": "a02fa879c405d6e301fc9db7668cbaf2d15ec229cc06c66d37a0d071bed81492",
      "lf_sha256": "a02fa879c405d6e301fc9db7668cbaf2d15ec229cc06c66d37a0d071bed81492"
    },
    "database/migrations/2026_08_25_163254_create_documents_table.php": {
      "sha256": "3355a8d29bb614e7b44e38cd70d2b5b2945a21471bddc331d04d20f6139db4c5",
      "lf_sha256": "3355a8d29bb614e7b44e38cd70d2b5b2945a21471bddc331d04d20f6139db4c5"
    },
    "database/migrations/2026_08_25_163255_create_document_versions_table.php": {
      "sha256": "4424873e78e07732480764fdd226ff9d0ed2eac2f73b0879325f3956b78364dc",
      "lf_sha256": "4424873e78e07732480764fdd226ff9d0ed2eac2f73b0879325f3956b78364dc"
    },
    "database/migrations/2026_08_27_170000_create_menus_table.php": {
      "sha256": "28b75705509a183f46aa0dda43c423148cddba75df8aa95c427cdc6b3377709b",
      "lf_sha256": "28b75705509a183f46aa0dda43c423148cddba75df8aa95c427cdc6b3377709b"
    },
    "database/migrations/2026_08_27_170001_create_menu_items_table.php": {
      "sha256": "c93780196f979710ab833aadc344c58b34795ebb706b78e6e478d5f57dc112c2",
      "lf_sha256": "c93780196f979710ab833aadc344c58b34795ebb706b78e6e478d5f57dc112c2"
    },
    "database/migrations/2026_08_27_170002_create_site_settings_table.php": {
      "sha256": "13a06d4d46a2c68e6f1ee7141c168970031144983768f740b6309f2b527ab4c3",
      "lf_sha256": "13a06d4d46a2c68e6f1ee7141c168970031144983768f740b6309f2b527ab4c3"
    },
    "database/migrations/2026_08_27_170003_create_social_links_table.php": {
      "sha256": "e54c86a838d5c3199a33a61755bff4a50b29b68ddef63aa719d1dead27818305",
      "lf_sha256": "e54c86a838d5c3199a33a61755bff4a50b29b68ddef63aa719d1dead27818305"
    },
    "database/migrations/2026_08_27_170004_create_hero_slides_table.php": {
      "sha256": "70306ad6dd5d9e56d4ba1af04146ba047a725a687695cc5e82ac674834374276",
      "lf_sha256": "70306ad6dd5d9e56d4ba1af04146ba047a725a687695cc5e82ac674834374276"
    },
    "database/migrations/2026_08_27_170005_create_contact_messages_table.php": {
      "sha256": "24a0185c49d2c2343d706b854a0a376d820dfc23078804d9e2e528a984b70adf",
      "lf_sha256": "24a0185c49d2c2343d706b854a0a376d820dfc23078804d9e2e528a984b70adf"
    },
    "database/migrations/2026_08_27_170006_create_redirects_table.php": {
      "sha256": "030f2405be220d4f7f352e24d94716d7893fdd82281b5534fe28e45402c8e31e",
      "lf_sha256": "030f2405be220d4f7f352e24d94716d7893fdd82281b5534fe28e45402c8e31e"
    },
    "database/migrations/2026_09_01_100000_add_show_title_to_pages_and_page_revisions.php": {
      "sha256": "a4b8848fb340b84395d452e65fd49797f50f2e03725c4797438f4c020c34f0ad",
      "lf_sha256": "a4b8848fb340b84395d452e65fd49797f50f2e03725c4797438f4c020c34f0ad"
    },
    "database/migrations/2026_09_01_113000_add_editor_mode_to_pages_and_page_revisions.php": {
      "sha256": "02b6ac33bb402aaf26f50beb3b6001335a78e0063470c7d7d97f22f304cd26e7",
      "lf_sha256": "02b6ac33bb402aaf26f50beb3b6001335a78e0063470c7d7d97f22f304cd26e7"
    },
    "database/migrations/2026_09_01_130000_add_localisation_to_pages.php": {
      "sha256": "fa96bacb666a1658f40564ab49938cd9a5ae245d8c7891efc36f0ddb349749f6",
      "lf_sha256": "fa96bacb666a1658f40564ab49938cd9a5ae245d8c7891efc36f0ddb349749f6"
    },
    "database/migrations/2026_09_02_140000_add_localisation_and_editor_mode_to_news.php": {
      "sha256": "df6a5af3c91810f8e44e2c07159a450e63d9b2d583ff720f635b8258ea4f7122",
      "lf_sha256": "df6a5af3c91810f8e44e2c07159a450e63d9b2d583ff720f635b8258ea4f7122"
    },
    "database/migrations/2026_09_02_143000_create_news_images_table.php": {
      "sha256": "ec5ce0c5ee130bf114829d857d4f3e31c64ef996929ac690a49461ad370042b9",
      "lf_sha256": "ec5ce0c5ee130bf114829d857d4f3e31c64ef996929ac690a49461ad370042b9"
    },
    "database/migrations/2026_09_02_160000_create_menu_item_translations_table.php": {
      "sha256": "91f39ceefb7d3a13dcec7f248ffaf46ffe22eec42e7f19e69c6f37c7a3128e16",
      "lf_sha256": "91f39ceefb7d3a13dcec7f248ffaf46ffe22eec42e7f19e69c6f37c7a3128e16"
    },
    "database/migrations/2026_09_03_090000_create_theme_layouts_tables.php": {
      "sha256": "7bdb60bc6b545f8705a03b68547c076d23ffb9719aee3925bf3c58cd0b42bb46",
      "lf_sha256": "7bdb60bc6b545f8705a03b68547c076d23ffb9719aee3925bf3c58cd0b42bb46"
    },
    "database/migrations/2026_09_03_110000_add_locale_to_theme_layouts_table.php": {
      "sha256": "c5db8db97c8abb4eb15ba38c9baa1a699093f24ade6b7781ac03a7bfa021dbb1",
      "lf_sha256": "c5db8db97c8abb4eb15ba38c9baa1a699093f24ade6b7781ac03a7bfa021dbb1"
    },
    "database/migrations/2026_09_07_120621_create_hero_slide_translations_table.php": {
      "sha256": "c56a1cf9376ea00d17d13d7e317908f04fd3f8338c0499162647b4d4abe8c695",
      "lf_sha256": "c56a1cf9376ea00d17d13d7e317908f04fd3f8338c0499162647b4d4abe8c695"
    },
    "database/migrations/2026_09_09_112158_create_school_leaders_table.php": {
      "sha256": "3ae16696365fef429ede7d8ba2f574045638dcf92798795b60e821d8149c749b",
      "lf_sha256": "3ae16696365fef429ede7d8ba2f574045638dcf92798795b60e821d8149c749b"
    },
    "database/migrations/2026_09_09_130000_create_past_commandants_table.php": {
      "sha256": "d3b2689e6cba25f341acc34bac10289e0572600dfdf285fbcc037c6c5531e630",
      "lf_sha256": "d3b2689e6cba25f341acc34bac10289e0572600dfdf285fbcc037c6c5531e630"
    },
    "database/migrations/2026_09_11_130000_create_past_chief_instructors_table.php": {
      "sha256": "8537a55ec16fd2881b8dbe7d1fcaa8d0070f80b82763282b2d056487c7a03874",
      "lf_sha256": "8537a55ec16fd2881b8dbe7d1fcaa8d0070f80b82763282b2d056487c7a03874"
    },
    "database/migrations/2026_09_23_120000_add_show_in_gallery_to_news.php": {
      "sha256": "32c8f25e8fe01a44bc2eddb1510f36cd58c58a42db38104c2458c8be22ef89ed",
      "lf_sha256": "32c8f25e8fe01a44bc2eddb1510f36cd58c58a42db38104c2458c8be22ef89ed"
    },
    "database/migrations/2026_09_28_090000_add_sinhala_title_to_galleries_table.php": {
      "sha256": "586c27e4765e813dececeddd8ed35a9308696454f73175d4331c1d9ca6fb4348",
      "lf_sha256": "586c27e4765e813dececeddd8ed35a9308696454f73175d4331c1d9ca6fb4348"
    },
    "database/migrations/2026_09_28_093500_add_sinhala_fields_to_site_settings_table.php": {
      "sha256": "815f582246704d254e1d674a5244b5155c07148b193f556fe236c7b031c83006",
      "lf_sha256": "815f582246704d254e1d674a5244b5155c07148b193f556fe236c7b031c83006"
    },
    "database/migrations/2026_09_29_082712_add_sinhala_fields_to_documents_table.php": {
      "sha256": "1be844c505024735f3bda03a9b9690d4f55f771b3037c6eab4e567c7522b737b",
      "lf_sha256": "1be844c505024735f3bda03a9b9690d4f55f771b3037c6eab4e567c7522b737b"
    },
    "database/migrations/2026_09_29_114230_add_current_version_si_to_documents_table.php": {
      "sha256": "4284b1657ffb3cfbc07ec79e566c0f610aad40f4740365273d54d03b6944892e",
      "lf_sha256": "4284b1657ffb3cfbc07ec79e566c0f610aad40f4740365273d54d03b6944892e"
    },
    "database/migrations/2026_10_01_150056_add_history_fields_to_school_leaders_table.php": {
      "sha256": "1dafc1537219382d1aa997f33d95d050e88d2ad8449983770ad8bac08b99c552",
      "lf_sha256": "1dafc1537219382d1aa997f33d95d050e88d2ad8449983770ad8bac08b99c552"
    },
    "database/migrations/2026_10_01_191603_drop_unique_role_key_from_school_leaders_table.php": {
      "sha256": "0fd0ede1edee341355891e2f3e40d4ab0231c0c8f690259fcc364ff60577c7d7",
      "lf_sha256": "0fd0ede1edee341355891e2f3e40d4ab0231c0c8f690259fcc364ff60577c7d7"
    },
    "database/migrations/2026_10_02_075947_create_school_leadership_positions_table.php": {
      "sha256": "1ed7b3e7a558bf1c371a4ef7cf1b8cf0ce8bb8ab64a0ad388a866103c9c66739",
      "lf_sha256": "1ed7b3e7a558bf1c371a4ef7cf1b8cf0ce8bb8ab64a0ad388a866103c9c66739"
    },
    "database/migrations/2026_10_02_075948_add_position_id_to_school_leaders_table.php": {
      "sha256": "61278ac495085bbdcb5adbbbb19b15ab5b1c31eeba3637f2bfca5291cd8289a0",
      "lf_sha256": "61278ac495085bbdcb5adbbbb19b15ab5b1c31eeba3637f2bfca5291cd8289a0"
    },
    "database/migrations/2026_10_19_120000_add_profiles_to_school_leaders_table.php": {
      "sha256": "69004755d6ee7e114cc426af429c39e16a7da741ad306440d6c5891a67ac33c4",
      "lf_sha256": "69004755d6ee7e114cc426af429c39e16a7da741ad306440d6c5891a67ac33c4"
    },
    "database/migrations/2026_10_20_120000_localize_school_footer_content.php": {
      "sha256": "b0cfe4b06c316e9735d3ea9d8a371d2733c67cc6f65ff1809ab991b940c04f87",
      "lf_sha256": "b0cfe4b06c316e9735d3ea9d8a371d2733c67cc6f65ff1809ab991b940c04f87"
    },
    "database/migrations/2026_10_20_130000_match_school_footer_layouts.php": {
      "sha256": "dadbd2b2a10b8a06c9946da4eaca0d3443e60898d4518b51dfd00c26d6b36278",
      "lf_sha256": "dadbd2b2a10b8a06c9946da4eaca0d3443e60898d4518b51dfd00c26d6b36278"
    },
    "database/migrations/2026_10_21_120000_add_custom_css_to_pages_and_page_revisions.php": {
      "sha256": "312662863a87083c939c13aac54f90ef7df55b0ddde8f1f19595ae25d522579c",
      "lf_sha256": "312662863a87083c939c13aac54f90ef7df55b0ddde8f1f19595ae25d522579c"
    },
    "database/migrations/2026_10_22_160000_add_public_filename_to_media_assets_table.php": {
      "sha256": "726cc412000aca8eb09fb7eb748c8d4527acb2a9b95a59b81ea50b6c68476c4a",
      "lf_sha256": "726cc412000aca8eb09fb7eb748c8d4527acb2a9b95a59b81ea50b6c68476c4a",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "726cc412000aca8eb09fb7eb748c8d4527acb2a9b95a59b81ea50b6c68476c4a",
          "lf_sha256": "726cc412000aca8eb09fb7eb748c8d4527acb2a9b95a59b81ea50b6c68476c4a"
        }
      ]
    },
    "database/seeders/DatabaseSeeder.php": {
      "sha256": "83790f1be2250b8adb88f95050c13053b0e74478c0a06a9caaf27af07128dfde",
      "lf_sha256": "83790f1be2250b8adb88f95050c13053b0e74478c0a06a9caaf27af07128dfde"
    },
    "database/seeders/RetireArchivePermissionsSeeder.php": {
      "sha256": "79c035f333c866525054842abac0bf488549b98c9ee0976a158918a2c3f9bace",
      "lf_sha256": "79c035f333c866525054842abac0bf488549b98c9ee0976a158918a2c3f9bace"
    },
    "database/seeders/RolesAndPermissionsSeeder.php": {
      "sha256": "7fec05da01997720f21ab36d77d0b04884e859718bdf7b4c35794e65daae880a",
      "lf_sha256": "7fec05da01997720f21ab36d77d0b04884e859718bdf7b4c35794e65daae880a"
    },
    "database/seeders/SchoolLeadersSeeder.php": {
      "sha256": "2392fbdf930f9465839b2c16a70a04b74df032a1fc33c7c09cc600ed602d59d3",
      "lf_sha256": "2392fbdf930f9465839b2c16a70a04b74df032a1fc33c7c09cc600ed602d59d3"
    },
    "database/seeders/SchoolModulePermissionsSeeder.php": {
      "sha256": "cef7fae3bb74c139cd7059f9a96dd722c2ad58c6c865b739e8279b696a972b41",
      "lf_sha256": "cef7fae3bb74c139cd7059f9a96dd722c2ad58c6c865b739e8279b696a972b41"
    },
    "lang/en/pagination.php": {
      "sha256": "11e0235b518a75ea3a26dd5d350804646924d2ac1f9d03872a0a4f6c3d241433",
      "lf_sha256": "11e0235b518a75ea3a26dd5d350804646924d2ac1f9d03872a0a4f6c3d241433"
    },
    "lang/en/public.php": {
      "sha256": "8d82e3d902aa65b41782e0e394bcfc564ed25a046fb4240275f81ca52c22af70",
      "lf_sha256": "8d82e3d902aa65b41782e0e394bcfc564ed25a046fb4240275f81ca52c22af70"
    },
    "lang/si/pagination.php": {
      "sha256": "84b6dd47aa063fffde54a094258770e9b40ca5d9325d3d6b931a79bab28ca2ad",
      "lf_sha256": "84b6dd47aa063fffde54a094258770e9b40ca5d9325d3d6b931a79bab28ca2ad"
    },
    "lang/si/public.php": {
      "sha256": "c9cefc37fa5d1c49fcea50a07f5caf0059888d3b54f087884eee19241cfabaa7",
      "lf_sha256": "c9cefc37fa5d1c49fcea50a07f5caf0059888d3b54f087884eee19241cfabaa7"
    },
    "lang/si/validation.php": {
      "sha256": "09d60d32ab4b1e4fbb1bf66282d74842e34ec5058063485e67da34361b83856b",
      "lf_sha256": "09d60d32ab4b1e4fbb1bf66282d74842e34ec5058063485e67da34361b83856b"
    },
    "lang/si.json": {
      "sha256": "577587c074b6118fcb32c08820c5df593b34f17489cd23692f8deb972279f79d",
      "lf_sha256": "577587c074b6118fcb32c08820c5df593b34f17489cd23692f8deb972279f79d"
    },
    "package-lock.json": {
      "sha256": "78b96f062dfc4fd518febfd4c5a42d947732bb7af51dd6154bd309c5502d76a8",
      "lf_sha256": "78b96f062dfc4fd518febfd4c5a42d947732bb7af51dd6154bd309c5502d76a8"
    },
    "package.json": {
      "sha256": "cd21b4b3efcdfcfa66e5121d8f41cd4b9d56ce91274356579a009783f901cce4",
      "lf_sha256": "cd21b4b3efcdfcfa66e5121d8f41cd4b9d56ce91274356579a009783f901cce4"
    },
    "public/.htaccess": {
      "sha256": "b7e379c77639fd56144947dbae84c84eb466d9c686ea81f2f013ae85421da923",
      "lf_sha256": "b7e379c77639fd56144947dbae84c84eb466d9c686ea81f2f013ae85421da923"
    },
    "public/build/assets/app-BydYg7V3.css": {
      "sha256": "901d8a7937b34e57d1f680324ace7ffbdc0e3fe198c2ca4efe677b734ace42b9",
      "lf_sha256": "901d8a7937b34e57d1f680324ace7ffbdc0e3fe198c2ca4efe677b734ace42b9",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "901d8a7937b34e57d1f680324ace7ffbdc0e3fe198c2ca4efe677b734ace42b9",
          "lf_sha256": "901d8a7937b34e57d1f680324ace7ffbdc0e3fe198c2ca4efe677b734ace42b9"
        }
      ]
    },
    "public/build/assets/app-qnY9enTG.js": {
      "sha256": "e6892afcad734e15bff622470a732c649af3083f9ea6800eda65512e59bf8f45",
      "lf_sha256": "e6892afcad734e15bff622470a732c649af3083f9ea6800eda65512e59bf8f45",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "e6892afcad734e15bff622470a732c649af3083f9ea6800eda65512e59bf8f45",
          "lf_sha256": "e6892afcad734e15bff622470a732c649af3083f9ea6800eda65512e59bf8f45"
        }
      ]
    },
    "public/build/assets/Exo2-Regular-D8MC3_dU.woff2": {
      "sha256": "49c9cd329161f5c5feed3ae80a788199f2dce127c00828e027a61261cf602491",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "49c9cd329161f5c5feed3ae80a788199f2dce127c00828e027a61261cf602491"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "49c9cd329161f5c5feed3ae80a788199f2dce127c00828e027a61261cf602491"
        }
      ]
    },
    "public/build/assets/fonts-C9MNnjVw.css": {
      "sha256": "3846a9bb60bfad92960eadcccaf0b4e129d74b11d48791506c8d1a827a385cc8",
      "lf_sha256": "3846a9bb60bfad92960eadcccaf0b4e129d74b11d48791506c8d1a827a385cc8",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "3846a9bb60bfad92960eadcccaf0b4e129d74b11d48791506c8d1a827a385cc8",
          "lf_sha256": "3846a9bb60bfad92960eadcccaf0b4e129d74b11d48791506c8d1a827a385cc8"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "3846a9bb60bfad92960eadcccaf0b4e129d74b11d48791506c8d1a827a385cc8",
          "lf_sha256": "3846a9bb60bfad92960eadcccaf0b4e129d74b11d48791506c8d1a827a385cc8"
        }
      ]
    },
    "public/build/assets/instrument-sans-400-normal-D1W7dsQl.woff": {
      "sha256": "797bd3eaa70edbd4757650ab44dad75fbfbfdf18bacedb4b71022c04621d30ad",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "797bd3eaa70edbd4757650ab44dad75fbfbfdf18bacedb4b71022c04621d30ad"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "797bd3eaa70edbd4757650ab44dad75fbfbfdf18bacedb4b71022c04621d30ad"
        }
      ]
    },
    "public/build/assets/instrument-sans-400-normal-DRC__1Mx.woff2": {
      "sha256": "9a91efaa3599a6ef01b2f49e9ec670e3a2f39baeef09c73198f1052cf3bb0850",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "9a91efaa3599a6ef01b2f49e9ec670e3a2f39baeef09c73198f1052cf3bb0850"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "9a91efaa3599a6ef01b2f49e9ec670e3a2f39baeef09c73198f1052cf3bb0850"
        }
      ]
    },
    "public/build/assets/instrument-sans-500-normal-Dk9ku72i.woff2": {
      "sha256": "9d3547da16d950d12a677c772a3ff9b69846e60e7dcd1b5efd540993f7286532",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "9d3547da16d950d12a677c772a3ff9b69846e60e7dcd1b5efd540993f7286532"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "9d3547da16d950d12a677c772a3ff9b69846e60e7dcd1b5efd540993f7286532"
        }
      ]
    },
    "public/build/assets/instrument-sans-500-normal-Z6ESRlEs.woff": {
      "sha256": "bd6a8dc8b0dc0cced0c32d3eda93f0e0e82c58ddefea8b53b3307b0c48da3089",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "bd6a8dc8b0dc0cced0c32d3eda93f0e0e82c58ddefea8b53b3307b0c48da3089"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "bd6a8dc8b0dc0cced0c32d3eda93f0e0e82c58ddefea8b53b3307b0c48da3089"
        }
      ]
    },
    "public/build/assets/instrument-sans-600-normal-B7fBEWYG.woff2": {
      "sha256": "efb770916eda95a0e31d80cccde9651d4e8617010b21220e9f227c4820d675da",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "efb770916eda95a0e31d80cccde9651d4e8617010b21220e9f227c4820d675da"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "efb770916eda95a0e31d80cccde9651d4e8617010b21220e9f227c4820d675da"
        }
      ]
    },
    "public/build/assets/instrument-sans-600-normal-B9e8oLYv.woff": {
      "sha256": "9b4ce1f2b26be7cb087fd87d6f99a202136292ec820af75bd38ab9e53895cf28",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "9b4ce1f2b26be7cb087fd87d6f99a202136292ec820af75bd38ab9e53895cf28"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "9b4ce1f2b26be7cb087fd87d6f99a202136292ec820af75bd38ab9e53895cf28"
        }
      ]
    },
    "public/build/assets/passkeys-ZWACIUjA.js": {
      "sha256": "cad93cdd93af65f3911cafade1b188cd55ed2300efe4efea27dde56f40d067f5",
      "lf_sha256": "cad93cdd93af65f3911cafade1b188cd55ed2300efe4efea27dde56f40d067f5",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "cad93cdd93af65f3911cafade1b188cd55ed2300efe4efea27dde56f40d067f5",
          "lf_sha256": "cad93cdd93af65f3911cafade1b188cd55ed2300efe4efea27dde56f40d067f5"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "cad93cdd93af65f3911cafade1b188cd55ed2300efe4efea27dde56f40d067f5",
          "lf_sha256": "cad93cdd93af65f3911cafade1b188cd55ed2300efe4efea27dde56f40d067f5"
        }
      ]
    },
    "public/build/fonts-manifest.json": {
      "sha256": "66edf17c93351fe01158be7414a441a762f106417c74351881876b405b8b10ca",
      "lf_sha256": "66edf17c93351fe01158be7414a441a762f106417c74351881876b405b8b10ca",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "66edf17c93351fe01158be7414a441a762f106417c74351881876b405b8b10ca",
          "lf_sha256": "66edf17c93351fe01158be7414a441a762f106417c74351881876b405b8b10ca"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "66edf17c93351fe01158be7414a441a762f106417c74351881876b405b8b10ca",
          "lf_sha256": "66edf17c93351fe01158be7414a441a762f106417c74351881876b405b8b10ca"
        }
      ]
    },
    "public/build/manifest.json": {
      "sha256": "05d935e3e0c3868c8f22ddd3c8f50e8a3fdc78d3ecb92feabdef8eaefb59b055",
      "lf_sha256": "05d935e3e0c3868c8f22ddd3c8f50e8a3fdc78d3ecb92feabdef8eaefb59b055",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "feac10f662e2bf41f9e836810323a39c9c28d1aca1529484b2b04825d42ce3e5",
          "lf_sha256": "feac10f662e2bf41f9e836810323a39c9c28d1aca1529484b2b04825d42ce3e5"
        },
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "05d935e3e0c3868c8f22ddd3c8f50e8a3fdc78d3ecb92feabdef8eaefb59b055",
          "lf_sha256": "05d935e3e0c3868c8f22ddd3c8f50e8a3fdc78d3ecb92feabdef8eaefb59b055"
        }
      ]
    },
    "public/index.php": {
      "sha256": "eba77cba39695b6bd091fe5211d481f7ebb2ce2d8d26230b5a609465d0a4aff9",
      "lf_sha256": "eba77cba39695b6bd091fe5211d481f7ebb2ce2d8d26230b5a609465d0a4aff9"
    },
    "public/themes/school-of-signals/assets/css/navbar.css": {
      "sha256": "c25f6ccc602a8affae566b6d2aeec6a36d53e552be3eb13bb8d83c00a9a8d476",
      "lf_sha256": "c25f6ccc602a8affae566b6d2aeec6a36d53e552be3eb13bb8d83c00a9a8d476"
    },
    "public/themes/school-of-signals/assets/css/news.css": {
      "sha256": "52e853e2a59b643cda57f96e48e4001f424ca33db30c953666211de36747526b",
      "lf_sha256": "52e853e2a59b643cda57f96e48e4001f424ca33db30c953666211de36747526b"
    },
    "public/themes/school-of-signals/assets/css/theme.css": {
      "sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed",
      "lf_sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed",
      "previous": [
        {
          "release": "2026-10-21-hero-button-link-fix",
          "sha256": "182e04ae853a29ab1832f0ef9a400f8026d608b5a71d1bf137c3a6e12fbe797c",
          "lf_sha256": "182e04ae853a29ab1832f0ef9a400f8026d608b5a71d1bf137c3a6e12fbe797c"
        },
        {
          "release": "2026-10-22-after-1235",
          "sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed",
          "lf_sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed"
        }
      ]
    },
    "public/themes/school-of-signals/assets/fonts/Exo2-Regular.woff2": {
      "sha256": "49c9cd329161f5c5feed3ae80a788199f2dce127c00828e027a61261cf602491"
    },
    "public/themes/school-of-signals/assets/fonts/fontawesome-7/css/all.min.css": {
      "sha256": "3b6a75d6685ead460f8dae6b8de2b669fbd57a471d9228131e0fdb1be723f2a9",
      "lf_sha256": "3b6a75d6685ead460f8dae6b8de2b669fbd57a471d9228131e0fdb1be723f2a9"
    },
    "public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-brands-400.woff2": {
      "sha256": "b8da2c25347b69ad3d7b5b8346725c2b0d06e86712de316e9b0578528e9d4f40"
    },
    "public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-regular-400.woff2": {
      "sha256": "f4f5cc8867d30647f0ad918c01599fa8aa9657c39eb55f0fc610120986544385"
    },
    "public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-solid-900.woff2": {
      "sha256": "47b1a018f969189c59b87e9d23f9304db54dc9bdcecf00b216c23515f86826e4"
    },
    "public/themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-v4compatibility.woff2": {
      "sha256": "17256d81225ece54f4e0e3b3711997d094257197f6ac3e2e6ece9cfafa9f9e39"
    },
    "public/themes/school-of-signals/assets/fonts/NotoSerifSinhala-Regular.woff2": {
      "sha256": "c036699da9b7f88366c9a39dafd0d684f0cb467700dac640b51e51408ba772c5"
    },
    "public/themes/school-of-signals/assets/images/image-placeholder.svg": {
      "sha256": "4c146c9ce1f0822da90b9719ac04e49bfa677f85d968c41dccfe3e180ce1e5a2",
      "lf_sha256": "4c146c9ce1f0822da90b9719ac04e49bfa677f85d968c41dccfe3e180ce1e5a2"
    },
    "public/themes/school-of-signals/assets/images/logo.png": {
      "sha256": "37317a61007914b4333c8b953f36d9270c519e8866ba4bdb89071892de38c82b"
    },
    "public/themes/school-of-signals/assets/images/slsc_about.png": {
      "sha256": "5e655e6e4364b4f4b4428d78ece4402c1adc31c7713216f8df622033057acc56"
    },
    "public/themes/school-of-signals/assets/js/theme.js": {
      "sha256": "b08ce07e52b026fb39ef489e5ed957c4882d3bc82b40c14869665c0abc0309e0",
      "lf_sha256": "b08ce07e52b026fb39ef489e5ed957c4882d3bc82b40c14869665c0abc0309e0"
    },
    "resources/css/app.css": {
      "sha256": "b985626488aea397f79530d3a709735149261593abe7854f2e6965a15aa5143c",
      "lf_sha256": "b985626488aea397f79530d3a709735149261593abe7854f2e6965a15aa5143c"
    },
    "resources/fonts/Exo2-Regular.woff2": {
      "sha256": "49c9cd329161f5c5feed3ae80a788199f2dce127c00828e027a61261cf602491"
    },
    "resources/fonts/NotoSerifSinhala-Regular.woff2": {
      "sha256": "c036699da9b7f88366c9a39dafd0d684f0cb467700dac640b51e51408ba772c5"
    },
    "resources/js/admin-confirmations.js": {
      "sha256": "6e90b8536afa34852125e79e01bd95c6e0dfee5411dc8dbd17836a248eb6d103",
      "lf_sha256": "6e90b8536afa34852125e79e01bd95c6e0dfee5411dc8dbd17836a248eb6d103",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "6e90b8536afa34852125e79e01bd95c6e0dfee5411dc8dbd17836a248eb6d103",
          "lf_sha256": "6e90b8536afa34852125e79e01bd95c6e0dfee5411dc8dbd17836a248eb6d103"
        }
      ]
    },
    "resources/js/app.js": {
      "sha256": "70e164073c274f70fd6286a37f386fd54411ca3df5aef1792be6e826fbe08686",
      "lf_sha256": "70e164073c274f70fd6286a37f386fd54411ca3df5aef1792be6e826fbe08686",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "70e164073c274f70fd6286a37f386fd54411ca3df5aef1792be6e826fbe08686",
          "lf_sha256": "70e164073c274f70fd6286a37f386fd54411ca3df5aef1792be6e826fbe08686"
        }
      ]
    },
    "resources/js/copy-path.js": {
      "sha256": "1091a4275431d8e9ed155c131b583c44e40fb517543a92d1f42bf27770bd0382",
      "lf_sha256": "1091a4275431d8e9ed155c131b583c44e40fb517543a92d1f42bf27770bd0382",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "1091a4275431d8e9ed155c131b583c44e40fb517543a92d1f42bf27770bd0382",
          "lf_sha256": "1091a4275431d8e9ed155c131b583c44e40fb517543a92d1f42bf27770bd0382"
        }
      ]
    },
    "resources/js/page-content-editor.js": {
      "sha256": "2b60b0a5f264d2de6d00df36116aab4325879b9235003c6ecd2cada83a9a7404",
      "lf_sha256": "2b60b0a5f264d2de6d00df36116aab4325879b9235003c6ecd2cada83a9a7404",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "2b60b0a5f264d2de6d00df36116aab4325879b9235003c6ecd2cada83a9a7404",
          "lf_sha256": "2b60b0a5f264d2de6d00df36116aab4325879b9235003c6ecd2cada83a9a7404"
        }
      ]
    },
    "resources/js/passkeys.js": {
      "sha256": "8033413d8f8991957bade2e15451851367d2bb91bdeb6b0ba553e5f38dd7d142",
      "lf_sha256": "8033413d8f8991957bade2e15451851367d2bb91bdeb6b0ba553e5f38dd7d142"
    },
    "resources/tailwind/page-content-safelist.html": {
      "sha256": "fe773362cca7983617bffa2afe9c62438a4b107610c982fddac1414803034178",
      "lf_sha256": "fe773362cca7983617bffa2afe9c62438a4b107610c982fddac1414803034178"
    },
    "resources/views/admin/dashboard.blade.php": {
      "sha256": "9d92294b8f02d2c654420a5fdef996eabaeb2d5ccf1e7276ae5040cd30288221",
      "lf_sha256": "9d92294b8f02d2c654420a5fdef996eabaeb2d5ccf1e7276ae5040cd30288221"
    },
    "resources/views/admin/news/preview.blade.php": {
      "sha256": "c4355db80d428759af4eceaf2f7ff357d96b0a06c761b33fa603c154b57623c1",
      "lf_sha256": "c4355db80d428759af4eceaf2f7ff357d96b0a06c761b33fa603c154b57623c1",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "c4355db80d428759af4eceaf2f7ff357d96b0a06c761b33fa603c154b57623c1",
          "lf_sha256": "c4355db80d428759af4eceaf2f7ff357d96b0a06c761b33fa603c154b57623c1"
        },
        {
          "release": "2026-10-22-news-trash-fix",
          "sha256": "c4355db80d428759af4eceaf2f7ff357d96b0a06c761b33fa603c154b57623c1",
          "lf_sha256": "c4355db80d428759af4eceaf2f7ff357d96b0a06c761b33fa603c154b57623c1"
        }
      ]
    },
    "resources/views/admin/pages/preview.blade.php": {
      "sha256": "da2384f1382ef560c559dd3e4cc49f1b437623bc55be649cbaaebdc800a29437",
      "lf_sha256": "da2384f1382ef560c559dd3e4cc49f1b437623bc55be649cbaaebdc800a29437"
    },
    "resources/views/admin/partials/sidebar-navigation.blade.php": {
      "sha256": "9968c35ea0042bf32197627e6a66e080683bf267d4d36efb667a1842832a1714",
      "lf_sha256": "9968c35ea0042bf32197627e6a66e080683bf267d4d36efb667a1842832a1714",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "9968c35ea0042bf32197627e6a66e080683bf267d4d36efb667a1842832a1714",
          "lf_sha256": "9968c35ea0042bf32197627e6a66e080683bf267d4d36efb667a1842832a1714"
        }
      ]
    },
    "resources/views/components/admin/confirmation-dialog.blade.php": {
      "sha256": "5d9bd1ef41d40d428b0b32f0c5dae764eaef938b0bed421734201e373baf07ba",
      "lf_sha256": "5d9bd1ef41d40d428b0b32f0c5dae764eaef938b0bed421734201e373baf07ba",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "5d9bd1ef41d40d428b0b32f0c5dae764eaef938b0bed421734201e373baf07ba",
          "lf_sha256": "5d9bd1ef41d40d428b0b32f0c5dae764eaef938b0bed421734201e373baf07ba"
        }
      ]
    },
    "resources/views/components/admin/copy-path.blade.php": {
      "sha256": "448fc7c0d37b14afe382034b1c9978a08cb12dc2856c0bc0b3248b5d0cb5cb12",
      "lf_sha256": "448fc7c0d37b14afe382034b1c9978a08cb12dc2856c0bc0b3248b5d0cb5cb12",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "448fc7c0d37b14afe382034b1c9978a08cb12dc2856c0bc0b3248b5d0cb5cb12",
          "lf_sha256": "448fc7c0d37b14afe382034b1c9978a08cb12dc2856c0bc0b3248b5d0cb5cb12"
        }
      ]
    },
    "resources/views/components/admin/icon-action.blade.php": {
      "sha256": "c89933dafce4df156e663d9fac0a1a7d80c935407cb22914652cc877dbcb1001",
      "lf_sha256": "c89933dafce4df156e663d9fac0a1a7d80c935407cb22914652cc877dbcb1001",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "c89933dafce4df156e663d9fac0a1a7d80c935407cb22914652cc877dbcb1001",
          "lf_sha256": "c89933dafce4df156e663d9fac0a1a7d80c935407cb22914652cc877dbcb1001"
        }
      ]
    },
    "resources/views/components/admin/nav-link.blade.php": {
      "sha256": "22352476130d8a92886d7f1c9bfd2a127d620711167bb71c30ef8bc43e32db21",
      "lf_sha256": "22352476130d8a92886d7f1c9bfd2a127d620711167bb71c30ef8bc43e32db21"
    },
    "resources/views/components/admin/page-header.blade.php": {
      "sha256": "31df711df001c5385b077b402c33eb59a80a0b00758f54884f132703ab9d7acd",
      "lf_sha256": "31df711df001c5385b077b402c33eb59a80a0b00758f54884f132703ab9d7acd"
    },
    "resources/views/components/admin/school-leaders/\u26a1school-leadership-position-index.blade.php": {
      "sha256": "9741770d828d549f3c1af15f23a31a2c2fbe5f32f4b7a0afb626dcf89c6a2537",
      "lf_sha256": "9741770d828d549f3c1af15f23a31a2c2fbe5f32f4b7a0afb626dcf89c6a2537"
    },
    "resources/views/components/admin/stat-card.blade.php": {
      "sha256": "a5c93131e554af51995be0808b51c880e2d1b6c6f0a9cc6c2f7f275b1b4e97ee",
      "lf_sha256": "a5c93131e554af51995be0808b51c880e2d1b6c6f0a9cc6c2f7f275b1b4e97ee"
    },
    "resources/views/components/admin/status-badge.blade.php": {
      "sha256": "f0f934716d91c364d65cfe1f5a59349941bf9cafcdd3fec06ed6ad67141fe82c",
      "lf_sha256": "f0f934716d91c364d65cfe1f5a59349941bf9cafcdd3fec06ed6ad67141fe82c"
    },
    "resources/views/components/admin/users/\u26a1user-create.blade.php": {
      "sha256": "b98e9941d1139e2ac42e55a8829e4028d9735b1bf83aad2ca203a277bea8b611",
      "lf_sha256": "b98e9941d1139e2ac42e55a8829e4028d9735b1bf83aad2ca203a277bea8b611"
    },
    "resources/views/components/admin/users/\u26a1user-edit.blade.php": {
      "sha256": "01f2a710e8b4365f080e021f13a7152317c336f108b245ae20e6241dccedd266",
      "lf_sha256": "01f2a710e8b4365f080e021f13a7152317c336f108b245ae20e6241dccedd266"
    },
    "resources/views/components/admin/users/\u26a1user-index.blade.php": {
      "sha256": "f0121e947efe4235b73e8a350d26522b439bc6f8ad18a58f5af81a86420a85ee",
      "lf_sha256": "f0121e947efe4235b73e8a350d26522b439bc6f8ad18a58f5af81a86420a85ee"
    },
    "resources/views/components/app-logo-icon.blade.php": {
      "sha256": "09582d9111415ac504cef5ef8a6652ab2f7f547fdf50af4ee4bbe725a896d807",
      "lf_sha256": "09582d9111415ac504cef5ef8a6652ab2f7f547fdf50af4ee4bbe725a896d807"
    },
    "resources/views/components/app-logo.blade.php": {
      "sha256": "2169dd7c92f57f4bfabeeb4b0e7ef5ca48e5bfa0a03dcf0473a00655d5b0e014",
      "lf_sha256": "2169dd7c92f57f4bfabeeb4b0e7ef5ca48e5bfa0a03dcf0473a00655d5b0e014"
    },
    "resources/views/components/auth-header.blade.php": {
      "sha256": "f2970bc80db25fc00fb27526440d3f69869cafbfb23ab1e12fc02438de65a470",
      "lf_sha256": "f2970bc80db25fc00fb27526440d3f69869cafbfb23ab1e12fc02438de65a470"
    },
    "resources/views/components/auth-session-status.blade.php": {
      "sha256": "45e46754dbe94278d484097de8ba418b478411b4be820f2fdcea996cdeb9e294",
      "lf_sha256": "45e46754dbe94278d484097de8ba418b478411b4be820f2fdcea996cdeb9e294"
    },
    "resources/views/components/desktop-user-menu.blade.php": {
      "sha256": "6a2544fee57adc54e6e8bc307e2a7ee48c16e57f9b5d298c4eeaf5b00f691b5d",
      "lf_sha256": "6a2544fee57adc54e6e8bc307e2a7ee48c16e57f9b5d298c4eeaf5b00f691b5d"
    },
    "resources/views/components/forms/page-builder.blade.php": {
      "sha256": "58a038b9413902616cb3c8df8345db9ba17a8e8e63c450bc394c45b4eb2ac880",
      "lf_sha256": "f0cca21cfc3e9f3fbbe4dc4232ebac601b30ec32ad89ba37f9d86c09695f6113"
    },
    "resources/views/components/forms/page-content-editor.blade.php": {
      "sha256": "4a9e3d0b7d8a410ab12e1d00341f5b243992594f86beafdba000db42b47e4a69",
      "lf_sha256": "4a9e3d0b7d8a410ab12e1d00341f5b243992594f86beafdba000db42b47e4a69",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "4a9e3d0b7d8a410ab12e1d00341f5b243992594f86beafdba000db42b47e4a69",
          "lf_sha256": "4a9e3d0b7d8a410ab12e1d00341f5b243992594f86beafdba000db42b47e4a69"
        }
      ]
    },
    "resources/views/components/forms/page-custom-css.blade.php": {
      "sha256": "045621cbdc81e4bd0334d6e6302c8243996fa88f43d9a576177c25d56f28d836",
      "lf_sha256": "045621cbdc81e4bd0334d6e6302c8243996fa88f43d9a576177c25d56f28d836"
    },
    "resources/views/components/forms/page-seo-fields.blade.php": {
      "sha256": "0cc097c728ecc28e7516c27222d8acfb58453378d3e53cc71d0fcf4663d030e6",
      "lf_sha256": "8f8ac53fda607251fb0d511ba2dc11506188f82ee385c98af7e0306e6609131e"
    },
    "resources/views/components/forms/page-visual-editor.blade.php": {
      "sha256": "e5a191204615cca521f4616c5cc576482f3fa5f6f21a4168c08f044a3b7c93ed",
      "lf_sha256": "e5a191204615cca521f4616c5cc576482f3fa5f6f21a4168c08f044a3b7c93ed"
    },
    "resources/views/components/forms/rich-text-editor.blade.php": {
      "sha256": "3e2dd181fe33db316ba274ad494a0d6bd9fb390eecf9a57fdd6e03ec92bfd1fe",
      "lf_sha256": "6edcf198c6dbba17189aa2c494de4c1eeac97827bcd8eb230c92ccfe0357cd75"
    },
    "resources/views/components/layouts/admin.blade.php": {
      "sha256": "4685ae04a09a76e549f9315f479748f2d18a2ad1a5b242dd8f00d331bb646b4d",
      "lf_sha256": "4685ae04a09a76e549f9315f479748f2d18a2ad1a5b242dd8f00d331bb646b4d",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "4685ae04a09a76e549f9315f479748f2d18a2ad1a5b242dd8f00d331bb646b4d",
          "lf_sha256": "4685ae04a09a76e549f9315f479748f2d18a2ad1a5b242dd8f00d331bb646b4d"
        }
      ]
    },
    "resources/views/components/page/blocks.blade.php": {
      "sha256": "6b4fc9dfb2076c3a3f024338b53d2a925e996861117ab1cd68b84f3f8f6d1239",
      "lf_sha256": "d72f6aa531044d3d6e5152a9a76c5736b0510a2d7305d89c17f5ade3e593ad24"
    },
    "resources/views/components/page/custom-styles.blade.php": {
      "sha256": "6b717501c4c38c0fb3db008d0859b87cd90da10363acb64f31f4fe8d68559472",
      "lf_sha256": "6b717501c4c38c0fb3db008d0859b87cd90da10363acb64f31f4fe8d68559472"
    },
    "resources/views/components/passkey-registration.blade.php": {
      "sha256": "814555ea7259a0d7225752557ec67e69b00e640f66b4a6a48b2b91d53b29e55a",
      "lf_sha256": "814555ea7259a0d7225752557ec67e69b00e640f66b4a6a48b2b91d53b29e55a"
    },
    "resources/views/components/passkey-verify.blade.php": {
      "sha256": "45fee99a425b3a252f2eb19869874886f71b5909dc8b710de65b8c26d7ecdbae",
      "lf_sha256": "45fee99a425b3a252f2eb19869874886f71b5909dc8b710de65b8c26d7ecdbae"
    },
    "resources/views/components/placeholder-pattern.blade.php": {
      "sha256": "711f4cc33c5e9558ac36bf7fd5bba51e6b1f7e17d50d4d9ee239f03b1124127a",
      "lf_sha256": "711f4cc33c5e9558ac36bf7fd5bba51e6b1f7e17d50d4d9ee239f03b1124127a"
    },
    "resources/views/components/settings/layout.blade.php": {
      "sha256": "a8a5722c1ed37c0585b7fa27c45de6184c242604e6e16e5536afe27069cc44bd",
      "lf_sha256": "a8a5722c1ed37c0585b7fa27c45de6184c242604e6e16e5536afe27069cc44bd"
    },
    "resources/views/dashboard.blade.php": {
      "sha256": "e39f15493568c457c2a73db8ff487598f410332ad53392668f6b843306434f39",
      "lf_sha256": "e39f15493568c457c2a73db8ff487598f410332ad53392668f6b843306434f39"
    },
    "resources/views/flux/icon/book-open-text.blade.php": {
      "sha256": "72ca4ad75deb9c43fd3b969edbef8b795f16f5745b6c393b744b3159a1958401",
      "lf_sha256": "72ca4ad75deb9c43fd3b969edbef8b795f16f5745b6c393b744b3159a1958401"
    },
    "resources/views/flux/icon/chevrons-up-down.blade.php": {
      "sha256": "3d5d35856b9cefca1a37f0af68cd26e1a8ff8a16ec438d0f8f46d8e30410b382",
      "lf_sha256": "3d5d35856b9cefca1a37f0af68cd26e1a8ff8a16ec438d0f8f46d8e30410b382"
    },
    "resources/views/flux/icon/folder-git-2.blade.php": {
      "sha256": "dd5ad1096f33fa36455d46c7b2348df034c7e7074d291b4e7d6094437411c109",
      "lf_sha256": "dd5ad1096f33fa36455d46c7b2348df034c7e7074d291b4e7d6094437411c109"
    },
    "resources/views/flux/icon/layout-grid.blade.php": {
      "sha256": "8e2e462d7f4aa6713d447c961d565e17148379160bac805c6d530e3ab45300ba",
      "lf_sha256": "8e2e462d7f4aa6713d447c961d565e17148379160bac805c6d530e3ab45300ba"
    },
    "resources/views/flux/navlist/group.blade.php": {
      "sha256": "53153a006dafea43374779cab6ac12dadcb20589deaec49e1049024b418447b3",
      "lf_sha256": "53153a006dafea43374779cab6ac12dadcb20589deaec49e1049024b418447b3"
    },
    "resources/views/layouts/app/header.blade.php": {
      "sha256": "6ac2f0f8a4db2d2aebd6d33bdb2c07382be532638a9e17e0df45cf28739cc31c",
      "lf_sha256": "6ac2f0f8a4db2d2aebd6d33bdb2c07382be532638a9e17e0df45cf28739cc31c"
    },
    "resources/views/layouts/app/sidebar.blade.php": {
      "sha256": "ab6f373ba6ec06a6af2f604ea567554482849f80b1e91b8dac741d711fef3940",
      "lf_sha256": "ab6f373ba6ec06a6af2f604ea567554482849f80b1e91b8dac741d711fef3940"
    },
    "resources/views/layouts/app.blade.php": {
      "sha256": "d78ea13390ab0ffeac36465b55d741dd555a6c2d3230edc24c014c204b748ba3",
      "lf_sha256": "d78ea13390ab0ffeac36465b55d741dd555a6c2d3230edc24c014c204b748ba3"
    },
    "resources/views/layouts/auth/card.blade.php": {
      "sha256": "ac41c9fe6327697f5a74befddf90b7f58f8b64ab4beea7995ac9ff7f3d3b765b",
      "lf_sha256": "ac41c9fe6327697f5a74befddf90b7f58f8b64ab4beea7995ac9ff7f3d3b765b"
    },
    "resources/views/layouts/auth/login.blade.php": {
      "sha256": "a50b56f44a84c6e1b6a4a5665da72e91285439abe30a4740bf8a742ea6d5b4e2",
      "lf_sha256": "a50b56f44a84c6e1b6a4a5665da72e91285439abe30a4740bf8a742ea6d5b4e2"
    },
    "resources/views/layouts/auth/simple.blade.php": {
      "sha256": "b51f90c78660820b2f7d6b7eb76950abf83a91c8dd39fa2989afc56f1833633c",
      "lf_sha256": "b51f90c78660820b2f7d6b7eb76950abf83a91c8dd39fa2989afc56f1833633c"
    },
    "resources/views/layouts/auth/split.blade.php": {
      "sha256": "6e60823b314d8025f87a2682ddcc0455811a4fa423e7255fcea2d82096595e6d",
      "lf_sha256": "6e60823b314d8025f87a2682ddcc0455811a4fa423e7255fcea2d82096595e6d"
    },
    "resources/views/layouts/auth.blade.php": {
      "sha256": "7ea23d7ca9f927a789f2627dd8e4b5a5cf2fa4326af12ee51eb23d20f4e5faf5",
      "lf_sha256": "7ea23d7ca9f927a789f2627dd8e4b5a5cf2fa4326af12ee51eb23d20f4e5faf5"
    },
    "resources/views/layouts/public.blade.php": {
      "sha256": "759023b71aeea5e53959c2afebe093a02ad797f485ecb1e1b34fa3a8009d6d45",
      "lf_sha256": "759023b71aeea5e53959c2afebe093a02ad797f485ecb1e1b34fa3a8009d6d45"
    },
    "resources/views/livewire/admin/audit-logs/audit-log-index.blade.php": {
      "sha256": "5bbc9c32c5c940d7804180a0013602c80b46af515eb93e08772e696044998768",
      "lf_sha256": "5bbc9c32c5c940d7804180a0013602c80b46af515eb93e08772e696044998768"
    },
    "resources/views/livewire/admin/contact-messages/contact-message-index.blade.php": {
      "sha256": "33b901387c1e5693e8e03cd836110aaea97bc5b59bce5280a80b8b175f9e02a7",
      "lf_sha256": "33b901387c1e5693e8e03cd836110aaea97bc5b59bce5280a80b8b175f9e02a7"
    },
    "resources/views/livewire/admin/contact-messages/contact-message-nav-link.blade.php": {
      "sha256": "862ab767ad079d7653e152972d31c961f21f91e4663f970d6799aec3f50002f8",
      "lf_sha256": "862ab767ad079d7653e152972d31c961f21f91e4663f970d6799aec3f50002f8",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "862ab767ad079d7653e152972d31c961f21f91e4663f970d6799aec3f50002f8",
          "lf_sha256": "862ab767ad079d7653e152972d31c961f21f91e4663f970d6799aec3f50002f8"
        }
      ]
    },
    "resources/views/livewire/admin/documents/document-category-index.blade.php": {
      "sha256": "07645376bc17b4aff83856bea606eccef0a88abeb2a197e8c3b0f3caa5463670",
      "lf_sha256": "07645376bc17b4aff83856bea606eccef0a88abeb2a197e8c3b0f3caa5463670"
    },
    "resources/views/livewire/admin/documents/document-create.blade.php": {
      "sha256": "c66d7ffb33251733a41d633771ed31b6c987e441321e9ab17e8a650d9dc21cc0",
      "lf_sha256": "c66d7ffb33251733a41d633771ed31b6c987e441321e9ab17e8a650d9dc21cc0"
    },
    "resources/views/livewire/admin/documents/document-edit.blade.php": {
      "sha256": "58233dd614a9d842ea2ef0099bb82ce401c57dc2fa6df2f1c63517052bbf3836",
      "lf_sha256": "58233dd614a9d842ea2ef0099bb82ce401c57dc2fa6df2f1c63517052bbf3836"
    },
    "resources/views/livewire/admin/documents/document-index.blade.php": {
      "sha256": "a388d4590ad902d8d25504fbda469af403304a718c9754e64ca2fb67d6862ee7",
      "lf_sha256": "a388d4590ad902d8d25504fbda469af403304a718c9754e64ca2fb67d6862ee7"
    },
    "resources/views/livewire/admin/documents/document-uploads.blade.php": {
      "sha256": "dfaf2a2657632859342ce428ed366b357425862e09e10dedfd639330e09ec5cd",
      "lf_sha256": "dfaf2a2657632859342ce428ed366b357425862e09e10dedfd639330e09ec5cd",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "dfaf2a2657632859342ce428ed366b357425862e09e10dedfd639330e09ec5cd",
          "lf_sha256": "dfaf2a2657632859342ce428ed366b357425862e09e10dedfd639330e09ec5cd"
        }
      ]
    },
    "resources/views/livewire/admin/galleries/gallery-create.blade.php": {
      "sha256": "e1189c894227352c61dd26ba72e6b269324e5b391f0cb609ab5c4af6b6f64034",
      "lf_sha256": "e1189c894227352c61dd26ba72e6b269324e5b391f0cb609ab5c4af6b6f64034"
    },
    "resources/views/livewire/admin/galleries/gallery-edit.blade.php": {
      "sha256": "e43626d9a634dd29e3bee26e96401cea13628ec6838c6af4bbca5f00fc338eec",
      "lf_sha256": "e43626d9a634dd29e3bee26e96401cea13628ec6838c6af4bbca5f00fc338eec"
    },
    "resources/views/livewire/admin/galleries/gallery-index.blade.php": {
      "sha256": "345b6c9f32a47c985c0626977757e4ffe1ac5924d5b0844d408dee3703857f4b",
      "lf_sha256": "345b6c9f32a47c985c0626977757e4ffe1ac5924d5b0844d408dee3703857f4b"
    },
    "resources/views/livewire/admin/hero-slides/hero-slide-index.blade.php": {
      "sha256": "52b8d4d13055e122f71d93a932a70411b9401c3fd043098b2b3b1969f285f2b5",
      "lf_sha256": "7e96f5b19f641e0ff74c44afa8b28373a7dc286bcd1cb0a029ca8dcf4b48e454"
    },
    "resources/views/livewire/admin/media/media-edit.blade.php": {
      "sha256": "53f4269d4134edd1c7fe520407439f7dffbab932088c11cedcef45ebaadbcd8b",
      "lf_sha256": "3d9d0e4aed1e4dc56d7b062b5ba1fc0a3401ffe766690d0220863f537a93c4fa",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "53f4269d4134edd1c7fe520407439f7dffbab932088c11cedcef45ebaadbcd8b",
          "lf_sha256": "3d9d0e4aed1e4dc56d7b062b5ba1fc0a3401ffe766690d0220863f537a93c4fa"
        }
      ]
    },
    "resources/views/livewire/admin/media/media-index.blade.php": {
      "sha256": "12639943afa885c90dc301ccdf806f4c339017cdcce62a96f5a283b358dc6f4d",
      "lf_sha256": "12639943afa885c90dc301ccdf806f4c339017cdcce62a96f5a283b358dc6f4d",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "12639943afa885c90dc301ccdf806f4c339017cdcce62a96f5a283b358dc6f4d",
          "lf_sha256": "12639943afa885c90dc301ccdf806f4c339017cdcce62a96f5a283b358dc6f4d"
        }
      ]
    },
    "resources/views/livewire/admin/media/media-upload.blade.php": {
      "sha256": "413a3742a65e5b3f276ad0fa10687d3338df2548b7ecaed4d63ea688189c1605",
      "lf_sha256": "413a3742a65e5b3f276ad0fa10687d3338df2548b7ecaed4d63ea688189c1605",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "413a3742a65e5b3f276ad0fa10687d3338df2548b7ecaed4d63ea688189c1605",
          "lf_sha256": "413a3742a65e5b3f276ad0fa10687d3338df2548b7ecaed4d63ea688189c1605"
        }
      ]
    },
    "resources/views/livewire/admin/news/news-category-index.blade.php": {
      "sha256": "2718e489690984770b6d493e1fbd40e4b717dbc775d0870978ff67a2b4dba4a4",
      "lf_sha256": "2718e489690984770b6d493e1fbd40e4b717dbc775d0870978ff67a2b4dba4a4"
    },
    "resources/views/livewire/admin/news/news-create.blade.php": {
      "sha256": "44d61fbef28a29338e31ce4562cc29562ae7847946aa7fc0c43e25a3502197ab",
      "lf_sha256": "44d61fbef28a29338e31ce4562cc29562ae7847946aa7fc0c43e25a3502197ab",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "44d61fbef28a29338e31ce4562cc29562ae7847946aa7fc0c43e25a3502197ab",
          "lf_sha256": "44d61fbef28a29338e31ce4562cc29562ae7847946aa7fc0c43e25a3502197ab"
        }
      ]
    },
    "resources/views/livewire/admin/news/news-edit.blade.php": {
      "sha256": "85d57153714299b38e99e869677c1eb5c9ed43df98d1811c0026d5eb2060b0ff",
      "lf_sha256": "85d57153714299b38e99e869677c1eb5c9ed43df98d1811c0026d5eb2060b0ff",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "85d57153714299b38e99e869677c1eb5c9ed43df98d1811c0026d5eb2060b0ff",
          "lf_sha256": "85d57153714299b38e99e869677c1eb5c9ed43df98d1811c0026d5eb2060b0ff"
        }
      ]
    },
    "resources/views/livewire/admin/news/news-index.blade.php": {
      "sha256": "c5aad3c4feb632420a2ca22601aa0ea309556a2571441fd0bb144c2ae6a51ce8",
      "lf_sha256": "c5aad3c4feb632420a2ca22601aa0ea309556a2571441fd0bb144c2ae6a51ce8",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "c5aad3c4feb632420a2ca22601aa0ea309556a2571441fd0bb144c2ae6a51ce8",
          "lf_sha256": "c5aad3c4feb632420a2ca22601aa0ea309556a2571441fd0bb144c2ae6a51ce8"
        },
        {
          "release": "2026-10-22-news-trash-fix",
          "sha256": "d2ff7bc84b7616f19506c777d9b3d1167d04e7817511f1eff0f86cbd4fc68ce3",
          "lf_sha256": "d2ff7bc84b7616f19506c777d9b3d1167d04e7817511f1eff0f86cbd4fc68ce3"
        }
      ]
    },
    "resources/views/livewire/admin/news/news-revision-history.blade.php": {
      "sha256": "75d6d42a25da12571e8bdf40cf03502958f5b6e643aaeb9afb39ecb5e2b2abce",
      "lf_sha256": "75d6d42a25da12571e8bdf40cf03502958f5b6e643aaeb9afb39ecb5e2b2abce"
    },
    "resources/views/livewire/admin/pages/page-create.blade.php": {
      "sha256": "568ec6b38e4d4a4565873387bae6b06ac3ade2f29673ed9b2d26f6d3ead3a8d5",
      "lf_sha256": "568ec6b38e4d4a4565873387bae6b06ac3ade2f29673ed9b2d26f6d3ead3a8d5",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "568ec6b38e4d4a4565873387bae6b06ac3ade2f29673ed9b2d26f6d3ead3a8d5",
          "lf_sha256": "568ec6b38e4d4a4565873387bae6b06ac3ade2f29673ed9b2d26f6d3ead3a8d5"
        }
      ]
    },
    "resources/views/livewire/admin/pages/page-edit.blade.php": {
      "sha256": "736fb76c2407f32d8ca52a5609f203621039c0f2d1beb0ac58cbd61d1e954a76",
      "lf_sha256": "736fb76c2407f32d8ca52a5609f203621039c0f2d1beb0ac58cbd61d1e954a76",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "736fb76c2407f32d8ca52a5609f203621039c0f2d1beb0ac58cbd61d1e954a76",
          "lf_sha256": "736fb76c2407f32d8ca52a5609f203621039c0f2d1beb0ac58cbd61d1e954a76"
        }
      ]
    },
    "resources/views/livewire/admin/pages/page-index.blade.php": {
      "sha256": "486658c88f33546dcb47350979491ca4038873de705aab130898272f37b381dd",
      "lf_sha256": "486658c88f33546dcb47350979491ca4038873de705aab130898272f37b381dd"
    },
    "resources/views/livewire/admin/pages/page-revision-history.blade.php": {
      "sha256": "a817436cb1ace0610ebae01e99c5e83895753a04b1ad5b2d2196c48d1c758730",
      "lf_sha256": "a817436cb1ace0610ebae01e99c5e83895753a04b1ad5b2d2196c48d1c758730"
    },
    "resources/views/livewire/admin/redirects/redirect-index.blade.php": {
      "sha256": "2cd284252ba6d4fc4201e3f3307f2a906ce6180c0103171b3b5a04130edb24e8",
      "lf_sha256": "2cd284252ba6d4fc4201e3f3307f2a906ce6180c0103171b3b5a04130edb24e8"
    },
    "resources/views/livewire/admin/roles/role-permission-index.blade.php": {
      "sha256": "4fef1dbf9bd13a96d6125a7b9bcd36442db43a4bcd7a505ac6987acd18a196ea",
      "lf_sha256": "4fef1dbf9bd13a96d6125a7b9bcd36442db43a4bcd7a505ac6987acd18a196ea"
    },
    "resources/views/livewire/admin/school-leaders/school-leader-index.blade.php": {
      "sha256": "b9db4f2cba033e0b39e55403ad3b9d361479c2e75b7bc60f82c05f597e1073fb",
      "lf_sha256": "b9db4f2cba033e0b39e55403ad3b9d361479c2e75b7bc60f82c05f597e1073fb"
    },
    "resources/views/livewire/admin/school-leaders/school-leader-profiles.blade.php": {
      "sha256": "4ed36041c401308acd631cea2804d2c887910c7bd234829762ec7925efe54374",
      "lf_sha256": "4ed36041c401308acd631cea2804d2c887910c7bd234829762ec7925efe54374"
    },
    "resources/views/livewire/admin/school-leaders/school-leadership-position-index.blade.php": {
      "sha256": "0f9e3f14d4d046cd31a397103d39b45230a4bbbd6aec76caf136d8dc50e0cde2",
      "lf_sha256": "0f9e3f14d4d046cd31a397103d39b45230a4bbbd6aec76caf136d8dc50e0cde2"
    },
    "resources/views/livewire/admin/settings/site-settings-index.blade.php": {
      "sha256": "dbb001876820c058ea1877aeda7ab0daccb4f164ace78be09f95449fd303e6e9",
      "lf_sha256": "dbb001876820c058ea1877aeda7ab0daccb4f164ace78be09f95449fd303e6e9",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "dbb001876820c058ea1877aeda7ab0daccb4f164ace78be09f95449fd303e6e9",
          "lf_sha256": "dbb001876820c058ea1877aeda7ab0daccb4f164ace78be09f95449fd303e6e9"
        }
      ]
    },
    "resources/views/livewire/admin/theme-layouts/theme-layout-index.blade.php": {
      "sha256": "0676b4ca531eb6393d8936ab74c171a848652a8b8d76aa2f6d7efdc366fad70a",
      "lf_sha256": "0676b4ca531eb6393d8936ab74c171a848652a8b8d76aa2f6d7efdc366fad70a"
    },
    "resources/views/livewire/admin/users/audit-logs/audit-log-index.blade.php": {
      "sha256": "97102fda810ed06740382350ba9f372f97cef0dc518ea044c0f8740f27b6d410",
      "lf_sha256": "97102fda810ed06740382350ba9f372f97cef0dc518ea044c0f8740f27b6d410"
    },
    "resources/views/livewire/admin/users/user-create.blade.php": {
      "sha256": "724a7e89bbec19e6deeab5fe29ae93570cbec4cb5c020c18b1551490930c9db8",
      "lf_sha256": "724a7e89bbec19e6deeab5fe29ae93570cbec4cb5c020c18b1551490930c9db8"
    },
    "resources/views/livewire/admin/users/user-edit.blade.php": {
      "sha256": "cf8383a001a7772742be157606df42b397092848a3eb8b343729bc733e147651",
      "lf_sha256": "cf8383a001a7772742be157606df42b397092848a3eb8b343729bc733e147651"
    },
    "resources/views/livewire/admin/users/user-index.blade.php": {
      "sha256": "a0f3f9ba9eb68d051017ca35708fafefe9894a296ba6e7a47c4144337f3c1eba",
      "lf_sha256": "a0f3f9ba9eb68d051017ca35708fafefe9894a296ba6e7a47c4144337f3c1eba"
    },
    "resources/views/livewire/auth/confirm-password.blade.php": {
      "sha256": "652d398191596b38c0bb8e5ae8f2f657e052cbe5ba5bc7acffef0e7695f86086",
      "lf_sha256": "652d398191596b38c0bb8e5ae8f2f657e052cbe5ba5bc7acffef0e7695f86086"
    },
    "resources/views/livewire/auth/forgot-password.blade.php": {
      "sha256": "857d1331e4a0a4f46f1243d0f9fc317379136680f688d6d3f4f370fe34c2f69c",
      "lf_sha256": "857d1331e4a0a4f46f1243d0f9fc317379136680f688d6d3f4f370fe34c2f69c"
    },
    "resources/views/livewire/auth/login.blade.php": {
      "sha256": "877e58451a608c777385ca955b32cf7189a022adc73cb12bbfc2b95f6b54190d",
      "lf_sha256": "877e58451a608c777385ca955b32cf7189a022adc73cb12bbfc2b95f6b54190d"
    },
    "resources/views/livewire/auth/register.blade.php": {
      "sha256": "0ccb079011cd8612f4ad52d6912ebaaa9f06a6a034c4c0d6042e0bc01e1a8545",
      "lf_sha256": "0ccb079011cd8612f4ad52d6912ebaaa9f06a6a034c4c0d6042e0bc01e1a8545"
    },
    "resources/views/livewire/auth/reset-password.blade.php": {
      "sha256": "8112e705efdc09c7e409de77368f928a66cf772d07dc45e1404fe300c7da630e",
      "lf_sha256": "8112e705efdc09c7e409de77368f928a66cf772d07dc45e1404fe300c7da630e"
    },
    "resources/views/livewire/auth/two-factor-challenge.blade.php": {
      "sha256": "483166e6adc0aa407a7dd8b89b3e1cb96dbf43eb5fe682328aff3e7ef6d4cd0c",
      "lf_sha256": "483166e6adc0aa407a7dd8b89b3e1cb96dbf43eb5fe682328aff3e7ef6d4cd0c"
    },
    "resources/views/livewire/auth/verify-email.blade.php": {
      "sha256": "ed657b90796dde599930acc235f852824eb9ac920b3158f5f6726ab1227eec6c",
      "lf_sha256": "ed657b90796dde599930acc235f852824eb9ac920b3158f5f6726ab1227eec6c"
    },
    "resources/views/livewire/settings/appearance.blade.php": {
      "sha256": "e57bf4b9f36dd03e0300db1712865fa016bdd8b52977d5ea2047322ba00258c0",
      "lf_sha256": "e57bf4b9f36dd03e0300db1712865fa016bdd8b52977d5ea2047322ba00258c0"
    },
    "resources/views/livewire/settings/delete-user-form.blade.php": {
      "sha256": "78083930ddb36ca0ef773528c4cf2d0bb10c26bb211fd99f0f7fb1145b569876",
      "lf_sha256": "78083930ddb36ca0ef773528c4cf2d0bb10c26bb211fd99f0f7fb1145b569876"
    },
    "resources/views/livewire/settings/profile.blade.php": {
      "sha256": "ce88f4c68108c3335e1ea38d96eed9fae599de4f93f495c8b86de0dc27a62e46",
      "lf_sha256": "ce88f4c68108c3335e1ea38d96eed9fae599de4f93f495c8b86de0dc27a62e46"
    },
    "resources/views/livewire/settings/security.blade.php": {
      "sha256": "f21a9f3d43bb6bf7ac980d79f050290a5d7b6fdcf2d5e5f8976565d7ac4fde1a",
      "lf_sha256": "f21a9f3d43bb6bf7ac980d79f050290a5d7b6fdcf2d5e5f8976565d7ac4fde1a"
    },
    "resources/views/livewire/settings/two-factor/recovery-codes.blade.php": {
      "sha256": "74dedd5a1d5071e3ed2a3c5239669d285dce2ff8de8a00fa6879b766d7dadd29",
      "lf_sha256": "74dedd5a1d5071e3ed2a3c5239669d285dce2ff8de8a00fa6879b766d7dadd29"
    },
    "resources/views/pages/settings/security.blade.php": {
      "sha256": "db6dbfb4ff97fe48ee19b3e5caeda7cc9dda13b6fdc018cad16e480d3e94c1af",
      "lf_sha256": "db6dbfb4ff97fe48ee19b3e5caeda7cc9dda13b6fdc018cad16e480d3e94c1af"
    },
    "resources/views/pages/show.blade.php": {
      "sha256": "70749c914ec301a6bee6256aa73d4107f44448816da3c5cd79f716adf2bdc6a9",
      "lf_sha256": "569875dfcd8201fd10dc744d9d0886b8c60a732bce744139f222c5966cb6a0c1"
    },
    "resources/views/partials/head.blade.php": {
      "sha256": "64afc29e7fdd085e1bb1d180dc54202d339ee5fc13f15c110a8bfc3cb3fa9a84",
      "lf_sha256": "64afc29e7fdd085e1bb1d180dc54202d339ee5fc13f15c110a8bfc3cb3fa9a84"
    },
    "resources/views/partials/settings-heading.blade.php": {
      "sha256": "5592534aba1afde73500aaabf238449f7170df01599d984cbcd154b54203ab42",
      "lf_sha256": "5592534aba1afde73500aaabf238449f7170df01599d984cbcd154b54203ab42"
    },
    "resources/views/public/contact/index.blade.php": {
      "sha256": "03687fe9eaff1ce56bc0fae439fcae7e61f22fa8ef5acf66fd4355839ff37dd7",
      "lf_sha256": "03687fe9eaff1ce56bc0fae439fcae7e61f22fa8ef5acf66fd4355839ff37dd7"
    },
    "resources/views/public/documents/index.blade.php": {
      "sha256": "8b4bf49d78be9e09dda167170fbd6202220629f47010705debe16c17bf2d5777",
      "lf_sha256": "8b4bf49d78be9e09dda167170fbd6202220629f47010705debe16c17bf2d5777"
    },
    "resources/views/public/documents/show.blade.php": {
      "sha256": "f0ca01aa387f888724ea798faaa393bb7990d0aea14778a3c90e1f0528eb9ec7",
      "lf_sha256": "f0ca01aa387f888724ea798faaa393bb7990d0aea14778a3c90e1f0528eb9ec7"
    },
    "resources/views/public/documents/styles.blade.php": {
      "sha256": "bca2a0f02dd9c2c52e652e287f6eb30accadc7ba5eb9986e9ba8906b0fb89254",
      "lf_sha256": "bca2a0f02dd9c2c52e652e287f6eb30accadc7ba5eb9986e9ba8906b0fb89254"
    },
    "resources/views/public/documents/upload-index.blade.php": {
      "sha256": "6fba1782c311e5535f1deb3a85e42abe6b2da599770f8e6162e83cefa2c085b9",
      "lf_sha256": "6fba1782c311e5535f1deb3a85e42abe6b2da599770f8e6162e83cefa2c085b9"
    },
    "resources/views/public/documents/upload-show.blade.php": {
      "sha256": "4c65c1aa770ef826a200a6723d8c48eab335c5d090b1f6c36446fb18b3a27923",
      "lf_sha256": "4c65c1aa770ef826a200a6723d8c48eab335c5d090b1f6c36446fb18b3a27923"
    },
    "resources/views/public/documents/upload-styles.blade.php": {
      "sha256": "3aad78284eb2b41669faa76cd37e429c3e76ef400eac71d28da64afd73bc07b0",
      "lf_sha256": "3aad78284eb2b41669faa76cd37e429c3e76ef400eac71d28da64afd73bc07b0"
    },
    "resources/views/public/galleries/index.blade.php": {
      "sha256": "cf1e9bb1e774264057bc2772a98fc0b4edad42560190451cafe2b4286030e8c1",
      "lf_sha256": "cf1e9bb1e774264057bc2772a98fc0b4edad42560190451cafe2b4286030e8c1"
    },
    "resources/views/public/galleries/news.blade.php": {
      "sha256": "737d1ed14c0d842887d24129f2be5d6764f378f7a19eeecaadc6fe08d3b35602",
      "lf_sha256": "737d1ed14c0d842887d24129f2be5d6764f378f7a19eeecaadc6fe08d3b35602"
    },
    "resources/views/public/galleries/pagination.blade.php": {
      "sha256": "632f8e0cacc9757d6404dd0bc7ab521adce5093ab8b60c24c5fa913a61790b7e",
      "lf_sha256": "632f8e0cacc9757d6404dd0bc7ab521adce5093ab8b60c24c5fa913a61790b7e"
    },
    "resources/views/public/galleries/show.blade.php": {
      "sha256": "8bb0771296f2c1e41881a03439195f82b3b7e8de954ec93e62da0bb044738bf7",
      "lf_sha256": "8bb0771296f2c1e41881a03439195f82b3b7e8de954ec93e62da0bb044738bf7"
    },
    "resources/views/public/galleries/styles.blade.php": {
      "sha256": "3f5b2ab758c0ada31910220ca81a8b03db0da7b76613df8308eb73d5f0d933b0",
      "lf_sha256": "3f5b2ab758c0ada31910220ca81a8b03db0da7b76613df8308eb73d5f0d933b0"
    },
    "resources/views/public/history/past-chief-instructors.blade.php": {
      "sha256": "12b7d65449cd006c38ca2b17fc11eded0e1f0aa7db0e2e02d8def95c2f6271da",
      "lf_sha256": "12b7d65449cd006c38ca2b17fc11eded0e1f0aa7db0e2e02d8def95c2f6271da"
    },
    "resources/views/public/history/past-sgt-major.blade.php": {
      "sha256": "44009b0d83b3a70ab2eca45bab2743ca76ce866d959f1746588dfbf15c84924a",
      "lf_sha256": "44009b0d83b3a70ab2eca45bab2743ca76ce866d959f1746588dfbf15c84924a"
    },
    "resources/views/public/home.blade.php": {
      "sha256": "793d7406c0458b89dcc734a13651942bd08fe79aa7253a05df01d7a6f7b7bbaf",
      "lf_sha256": "efb52aea1f59b3c338b5a0f0d53c9331134c604120988862d39790efcc3a6e46",
      "previous": [
        {
          "release": "2026-10-21-hero-button-link-fix",
          "sha256": "793d7406c0458b89dcc734a13651942bd08fe79aa7253a05df01d7a6f7b7bbaf",
          "lf_sha256": "efb52aea1f59b3c338b5a0f0d53c9331134c604120988862d39790efcc3a6e46"
        }
      ]
    },
    "resources/views/public/maintenance.blade.php": {
      "sha256": "5c7fb8d4d081350a44c9e2d2cdb54cedc3ad0adcd0dee780041280262ce67c8f",
      "lf_sha256": "5c7fb8d4d081350a44c9e2d2cdb54cedc3ad0adcd0dee780041280262ce67c8f"
    },
    "resources/views/public/news/index.blade.php": {
      "sha256": "d170b9db2f89cf59e6b97f0f7e8f15edcd6e4b7ecd4032257dd1b0574a8d2e1e",
      "lf_sha256": "d170b9db2f89cf59e6b97f0f7e8f15edcd6e4b7ecd4032257dd1b0574a8d2e1e"
    },
    "resources/views/public/news/show.blade.php": {
      "sha256": "8165749aa27c6bb9298c83679a3bee213dcdb1a98c422621e54dba8fc4b9001b",
      "lf_sha256": "8165749aa27c6bb9298c83679a3bee213dcdb1a98c422621e54dba8fc4b9001b"
    },
    "routes/console.php": {
      "sha256": "9adccc33e7dd400683e434774077c7fdb2f299c5712cedf16a43fdf56f2850fa",
      "lf_sha256": "9adccc33e7dd400683e434774077c7fdb2f299c5712cedf16a43fdf56f2850fa"
    },
    "routes/settings.php": {
      "sha256": "660be5c4e30ea1f66a5feb1bc87afa6171e85d7399881bfe9b679eeaa5049dbf",
      "lf_sha256": "660be5c4e30ea1f66a5feb1bc87afa6171e85d7399881bfe9b679eeaa5049dbf"
    },
    "routes/web.php": {
      "sha256": "9e5c7b803680badb9860df223401bb7bc83c7fc7109033e6ff11abbf07eb8b27",
      "lf_sha256": "9e5c7b803680badb9860df223401bb7bc83c7fc7109033e6ff11abbf07eb8b27",
      "previous": [
        {
          "release": "2026-10-22-media-confirmations",
          "sha256": "29dfa9e4fcad5a4369100135bc3bdaf51c26b7e32887f82bda0a4d772084cc57",
          "lf_sha256": "29dfa9e4fcad5a4369100135bc3bdaf51c26b7e32887f82bda0a4d772084cc57"
        },
        {
          "release": "2026-10-22-news-trash-fix",
          "sha256": "40632b87d501bcdff9b16188e7a9b176b3d2c72e6012e57f172af3a0017b05c6",
          "lf_sha256": "40632b87d501bcdff9b16188e7a9b176b3d2c72e6012e57f172af3a0017b05c6"
        }
      ]
    },
    "themes/school-of-signals/assets/css/navbar.css": {
      "sha256": "c25f6ccc602a8affae566b6d2aeec6a36d53e552be3eb13bb8d83c00a9a8d476",
      "lf_sha256": "c25f6ccc602a8affae566b6d2aeec6a36d53e552be3eb13bb8d83c00a9a8d476"
    },
    "themes/school-of-signals/assets/css/theme.css": {
      "sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed",
      "lf_sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed",
      "previous": [
        {
          "release": "2026-10-21-hero-button-link-fix",
          "sha256": "182e04ae853a29ab1832f0ef9a400f8026d608b5a71d1bf137c3a6e12fbe797c",
          "lf_sha256": "182e04ae853a29ab1832f0ef9a400f8026d608b5a71d1bf137c3a6e12fbe797c"
        },
        {
          "release": "2026-10-22-after-1235",
          "sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed",
          "lf_sha256": "26fb5dc44e877c7285c890c6e7772f313646fa26abbaa42902469d0483e1f6ed"
        }
      ]
    },
    "themes/school-of-signals/assets/fonts/Exo2-Regular.woff2": {
      "sha256": "49c9cd329161f5c5feed3ae80a788199f2dce127c00828e027a61261cf602491"
    },
    "themes/school-of-signals/assets/fonts/fontawesome-7/css/all.min.css": {
      "sha256": "3b6a75d6685ead460f8dae6b8de2b669fbd57a471d9228131e0fdb1be723f2a9",
      "lf_sha256": "3b6a75d6685ead460f8dae6b8de2b669fbd57a471d9228131e0fdb1be723f2a9"
    },
    "themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-brands-400.woff2": {
      "sha256": "b8da2c25347b69ad3d7b5b8346725c2b0d06e86712de316e9b0578528e9d4f40"
    },
    "themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-regular-400.woff2": {
      "sha256": "f4f5cc8867d30647f0ad918c01599fa8aa9657c39eb55f0fc610120986544385"
    },
    "themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-solid-900.woff2": {
      "sha256": "47b1a018f969189c59b87e9d23f9304db54dc9bdcecf00b216c23515f86826e4"
    },
    "themes/school-of-signals/assets/fonts/fontawesome-7/webfonts/fa-v4compatibility.woff2": {
      "sha256": "17256d81225ece54f4e0e3b3711997d094257197f6ac3e2e6ece9cfafa9f9e39"
    },
    "themes/school-of-signals/assets/fonts/NotoSerifSinhala-Regular.woff2": {
      "sha256": "c036699da9b7f88366c9a39dafd0d684f0cb467700dac640b51e51408ba772c5"
    },
    "themes/school-of-signals/assets/images/image-placeholder.svg": {
      "sha256": "4c146c9ce1f0822da90b9719ac04e49bfa677f85d968c41dccfe3e180ce1e5a2",
      "lf_sha256": "4c146c9ce1f0822da90b9719ac04e49bfa677f85d968c41dccfe3e180ce1e5a2"
    },
    "themes/school-of-signals/assets/images/logo.png": {
      "sha256": "37317a61007914b4333c8b953f36d9270c519e8866ba4bdb89071892de38c82b"
    },
    "themes/school-of-signals/assets/js/theme.js": {
      "sha256": "b08ce07e52b026fb39ef489e5ed957c4882d3bc82b40c14869665c0abc0309e0",
      "lf_sha256": "b08ce07e52b026fb39ef489e5ed957c4882d3bc82b40c14869665c0abc0309e0"
    },
    "themes/school-of-signals/lang/en/home.php": {
      "sha256": "5185e9782f811d8bc1b161d1db9f1c40fa313b53a29aed6822f53fc9a0bd3a4e",
      "lf_sha256": "5185e9782f811d8bc1b161d1db9f1c40fa313b53a29aed6822f53fc9a0bd3a4e",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "5185e9782f811d8bc1b161d1db9f1c40fa313b53a29aed6822f53fc9a0bd3a4e",
          "lf_sha256": "5185e9782f811d8bc1b161d1db9f1c40fa313b53a29aed6822f53fc9a0bd3a4e"
        }
      ]
    },
    "themes/school-of-signals/lang/si/home.php": {
      "sha256": "8313e0272ae780e5359742e87ae27ad95c9c7e58f4c6b315f55c4709c66a6c05",
      "lf_sha256": "8313e0272ae780e5359742e87ae27ad95c9c7e58f4c6b315f55c4709c66a6c05",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "8313e0272ae780e5359742e87ae27ad95c9c7e58f4c6b315f55c4709c66a6c05",
          "lf_sha256": "8313e0272ae780e5359742e87ae27ad95c9c7e58f4c6b315f55c4709c66a6c05"
        }
      ]
    },
    "themes/school-of-signals/theme.json": {
      "sha256": "601bf87f21e9859f5165d7dc2da7c7856673e0de1d5ce6fa6f2b70c3fb4ca67a",
      "lf_sha256": "601bf87f21e9859f5165d7dc2da7c7856673e0de1d5ce6fa6f2b70c3fb4ca67a"
    },
    "themes/school-of-signals/views/commandant.blade.php": {
      "sha256": "b9eedd649111fc80ed5cc09cf3828b15aecf4fcbe9c06bca5b521d77a8ab9351",
      "lf_sha256": "b9eedd649111fc80ed5cc09cf3828b15aecf4fcbe9c06bca5b521d77a8ab9351"
    },
    "themes/school-of-signals/views/history/past-commandants.blade.php": {
      "sha256": "179c90f3a4a31fbcdd2dc705ca85775594464c3a959b4386e533fa4dbb7008cb",
      "lf_sha256": "179c90f3a4a31fbcdd2dc705ca85775594464c3a959b4386e533fa4dbb7008cb"
    },
    "themes/school-of-signals/views/home.blade.php": {
      "sha256": "5731385c4198f7a86d61c25e903223d60193b6deea00f9ef3e74351b6b374942",
      "lf_sha256": "5731385c4198f7a86d61c25e903223d60193b6deea00f9ef3e74351b6b374942",
      "previous": [
        {
          "release": "2026-10-21-hero-button-link-fix",
          "sha256": "85d8457081661343e8ab1086560132f865aa13f34c9844482c7e5a1cee9ad64e",
          "lf_sha256": "85d8457081661343e8ab1086560132f865aa13f34c9844482c7e5a1cee9ad64e"
        },
        {
          "release": "2026-10-22-after-1235",
          "sha256": "5731385c4198f7a86d61c25e903223d60193b6deea00f9ef3e74351b6b374942",
          "lf_sha256": "5731385c4198f7a86d61c25e903223d60193b6deea00f9ef3e74351b6b374942"
        }
      ]
    },
    "themes/school-of-signals/views/layout.blade.php": {
      "sha256": "8011cdc1f7526fe233bea11e2343d7de857eccd50895c6e64ef6fde95c43ea67",
      "lf_sha256": "8011cdc1f7526fe233bea11e2343d7de857eccd50895c6e64ef6fde95c43ea67",
      "previous": [
        {
          "release": "2026-10-22-after-1235",
          "sha256": "8011cdc1f7526fe233bea11e2343d7de857eccd50895c6e64ef6fde95c43ea67",
          "lf_sha256": "8011cdc1f7526fe233bea11e2343d7de857eccd50895c6e64ef6fde95c43ea67"
        }
      ]
    },
    "themes/school-of-signals/views/leader-profile.blade.php": {
      "sha256": "dedd6a8448c2299bac6c46beb534afdb3e0490956ab0f3b91700724bbc5da3ae",
      "lf_sha256": "dedd6a8448c2299bac6c46beb534afdb3e0490956ab0f3b91700724bbc5da3ae"
    },
    "themes/school-of-signals/views/news/index.blade.php": {
      "sha256": "60d36a9994e56e188de4ed404a70d870141b308f756b7b375ee14ce3334fc0df",
      "lf_sha256": "60d36a9994e56e188de4ed404a70d870141b308f756b7b375ee14ce3334fc0df"
    },
    "themes/school-of-signals/views/news/show.blade.php": {
      "sha256": "a4e343652f6fb436780cce360333f288187b8d7f34b556e7361b8c90a9ab9a63",
      "lf_sha256": "a4e343652f6fb436780cce360333f288187b8d7f34b556e7361b8c90a9ab9a63"
    },
    "themes/school-of-signals/views/pages/show.blade.php": {
      "sha256": "2337dc9d77a3cdf992046b7c69caf4ad80061cfc7140b0ef4b91b0e568eecd4c",
      "lf_sha256": "2337dc9d77a3cdf992046b7c69caf4ad80061cfc7140b0ef4b91b0e568eecd4c"
    },
    "themes/school-of-signals/views/partials/cms-contact.blade.php": {
      "sha256": "0986fe44c1bb86bd52919828f838ab1d89a8de0d61c8a6783642341ab7f9d7da",
      "lf_sha256": "0986fe44c1bb86bd52919828f838ab1d89a8de0d61c8a6783642341ab7f9d7da"
    },
    "themes/school-of-signals/views/partials/commandant-feature.blade.php": {
      "sha256": "f25744709eece879ecc43522092c1b0120fe24d00b7afe57559cc76342dee06d",
      "lf_sha256": "f25744709eece879ecc43522092c1b0120fe24d00b7afe57559cc76342dee06d"
    },
    "themes/school-of-signals/views/partials/leadership-profile-content.blade.php": {
      "sha256": "d4ae65259403cae1430fb0881a3517e2ff54face3d78e4919aab8463d1187793",
      "lf_sha256": "d4ae65259403cae1430fb0881a3517e2ff54face3d78e4919aab8463d1187793"
    },
    "themes/school-of-signals/views/school-leadership.blade.php": {
      "sha256": "d56c6bf7b461c1456010d5a6ead68af6a7c372e7e76d59aa98da1255f76d072e",
      "lf_sha256": "d56c6bf7b461c1456010d5a6ead68af6a7c372e7e76d59aa98da1255f76d072e"
    },
    "vite.config.js": {
      "sha256": "2daed21312da86aff889ada1a53220398e56f0fae29f97bb088aa961eb5d1a43",
      "lf_sha256": "2daed21312da86aff889ada1a53220398e56f0fae29f97bb088aa961eb5d1a43"
    }
  }
}
MANIFEST, true, 512, JSON_THROW_ON_ERROR);

$root = realpath($argv[1] ?? getcwd());
if ($root === false || !is_file($root.'/artisan') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "Pass the existing Laravel project root containing artisan and bootstrap/app.php.\n");
    exit(1);
}

function insideRoot(string $path, string $root): bool
{
    $path = str_replace('\\', '/', $path);
    $prefix = rtrim(str_replace('\\', '/', $root), '/').'/';
    if (DIRECTORY_SEPARATOR === '\\') {
        return strncasecmp($path, $prefix, strlen($prefix)) === 0;
    }

    return strncmp($path, $prefix, strlen($prefix)) === 0;
}

function ignoredPath(string $path): bool
{
    return str_starts_with($path, 'bootstrap/cache/')
        || in_array('.env', explode('/', $path), true)
        || in_array('.git', explode('/', $path), true)
        || in_array('node_modules', explode('/', $path), true)
        || in_array('vendor', explode('/', $path), true)
        || str_ends_with($path, '/.gitignore');
}

$counts = array_fill_keys(['current', 'known_previous', 'different', 'missing', 'unsafe_or_unreadable'], 0);
$differences = [];
$migrationFiles = [];
foreach ($manifest['files'] as $relative => $expected) {
    if (str_contains($relative, '..') || str_starts_with($relative, '/') || str_contains($relative, '\\')) {
        fwrite(STDERR, "Invalid manifest path.\n");
        exit(1);
    }
    $path = $root.'/'.$relative;
    $status = 'missing';
    $detail = ['path' => $relative];
    if (file_exists($path) || is_link($path)) {
        $resolved = realpath($path);
        if ($resolved === false || !insideRoot($resolved, $root) || !is_file($path) || !is_readable($path)) {
            $status = 'unsafe_or_unreadable';
        } else {
            $hash = hash_file('sha256', $path);
            $lfHash = $hash;
            if (isset($expected['lf_sha256'])) {
                $contents = file_get_contents($path);
                $lfHash = $contents === false ? false : hash('sha256', str_replace("\r\n", "\n", $contents));
            }
            $detail['server_sha256'] = $hash;
            if ($hash === $expected['sha256'] || (isset($expected['lf_sha256']) && $lfHash === $expected['lf_sha256'])) {
                $status = 'current';
            } else {
                $status = 'different';
                foreach ($expected['previous'] ?? [] as $previous) {
                    if ($hash === $previous['sha256'] || (isset($previous['lf_sha256']) && $lfHash === $previous['lf_sha256'])) {
                        $status = 'known_previous';
                        $detail['matches_package'][] = $previous['release'];
                    }
                }
            }
        }
    }
    ++$counts[$status];
    if ($status !== 'current') {
        $detail['status'] = $status;
        $differences[] = $detail;
    }
    if (str_starts_with($relative, 'database/migrations/2026_10_')) {
        $migrationFiles[] = ['path' => $relative, 'file_status' => $status, 'database_applied_status' => 'not_checked'];
    }
}

$serverOnly = [];
$scanIssues = [];
$visited = 0;
foreach ($manifest['directories'] as $directory) {
    $base = $root.'/'.$directory;
    if (!is_dir($base)) {
        continue;
    }
    $resolved = realpath($base);
    if ($resolved === false || !insideRoot($resolved, $root) || is_link($base)) {
        $scanIssues[] = $directory;
        continue;
    }
    try {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (++$visited > 20000) {
                $scanIssues[] = 'File scan limit reached; server-only listing is incomplete.';
                break 2;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            if (ignoredPath($relative)) {
                continue;
            }
            $resolved = $file->getRealPath();
            if ($file->isLink() || $resolved === false || !insideRoot($resolved, $root)) {
                $scanIssues[] = $relative;
            } elseif ($file->isFile() && !isset($manifest['files'][$relative])) {
                $serverOnly[] = $relative;
            }
        }
    } catch (Throwable $error) {
        $scanIssues[] = $directory.': could not scan all entries';
    }
}
sort($serverOnly);
$report = [
    'project' => 'sch_of_slsc',
    'reference' => $manifest['reference'],
    'generated_at_utc' => gmdate('c'),
    'php_version' => PHP_VERSION,
    'local_reference_files' => count($manifest['files']),
    'counts' => $counts,
    'differences' => $differences,
    'server_only_in_compared_directories' => $serverOnly,
    'scan_issues' => $scanIssues,
    'recent_migration_files' => $migrationFiles,
    'notes' => [
        'Comparison only. No application files or database records were changed.',
        'Different files can contain hosting edits. Review before replacement.',
        'Server-only files are listed for review, not deletion. Old hashed assets can be harmless.',
        'Current means equal bytes or equal CRLF/LF-normalized text; it does not prove database compatibility.',
        'Database migration state, saved content, installed dependencies, .env, uploads and runtime caches were not inspected.',
        'Also collect: php artisan migrate:status --no-ansi',
    ],
];
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR).PHP_EOL;
exit($counts['unsafe_or_unreadable'] > 0 || $scanIssues !== [] ? 2 : 0);
