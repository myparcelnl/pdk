# Changelog

All notable changes to this project will be documented in this file. See
[Conventional Commits](https://conventionalcommits.org) for commit guidelines.

## [2.61.0](https://github.com/myparcelnl/pdk/compare/v2.60.1...v2.61.0) (2025-07-31)


### :bug: Bug Fixes

* small package price not saved after settings refresh in PDK plugin ([#374](https://github.com/myparcelnl/pdk/issues/374)) ([c06db90](https://github.com/myparcelnl/pdk/commit/c06db9032b447d9531b82eec6ecb1392a161af68))


### :sparkles: New Features

* replace UPS carrier 8 with carriers 12 and 13 ([#371](https://github.com/myparcelnl/pdk/issues/371)) ([5e3e2c6](https://github.com/myparcelnl/pdk/commit/5e3e2c65ce2d772ca7f62f43c4d992de78959969))

## [2.60.1](https://github.com/myparcelnl/pdk/compare/v2.60.0...v2.60.1) (2025-07-18)

## [2.60.0](https://github.com/myparcelnl/pdk/compare/v2.59.1...v2.60.0) (2025-07-17)


### :sparkles: New Features

* **export:** remove schema-based validation for order exports ([#368](https://github.com/myparcelnl/pdk/issues/368)) ([093edf2](https://github.com/myparcelnl/pdk/commit/093edf24f956aa4b56646b110f281f341092064b))

## [2.59.1](https://github.com/myparcelnl/pdk/compare/v2.59.0...v2.59.1) (2025-07-11)


### :bug: Bug Fixes

* fix unreachable settings page when order status is NULL ([#367](https://github.com/myparcelnl/pdk/issues/367)) ([9a16942](https://github.com/myparcelnl/pdk/commit/9a1694256023534241ba494ce3d60264387d3db3))

## [2.59.0](https://github.com/myparcelnl/pdk/compare/v2.58.6...v2.59.0) (2025-07-09)


### :sparkles: New Features

* show api errors ([#353](https://github.com/myparcelnl/pdk/issues/353)) ([b511e85](https://github.com/myparcelnl/pdk/commit/b511e8580c984b84d2523227f8f97a47d444ed66))

## [2.58.6](https://github.com/myparcelnl/pdk/compare/v2.58.5...v2.58.6) (2025-06-25)


### :bug: Bug Fixes

* **export:** fix an issue with missing delivery options when exporting ([#360](https://github.com/myparcelnl/pdk/issues/360)) ([eca4be9](https://github.com/myparcelnl/pdk/commit/eca4be9c3d5800845514c9c78c72fe7ca9de96d4))

## [2.58.5](https://github.com/myparcelnl/pdk/compare/v2.58.4...v2.58.5) (2025-06-13)


### :bug: Bug Fixes

* **pdk:** truncate address fields to max API length during export ([#361](https://github.com/myparcelnl/pdk/issues/361)) ([916f481](https://github.com/myparcelnl/pdk/commit/916f481f23fdbc5ab62ee6c17613d90c3a77db20))

## [2.58.4](https://github.com/myparcelnl/pdk/compare/v2.58.3...v2.58.4) (2025-06-12)


### :bug: Bug Fixes

* supply correct status setting in webhook ([#358](https://github.com/myparcelnl/pdk/issues/358)) ([d5c1495](https://github.com/myparcelnl/pdk/commit/d5c1495414244726a2737945584e5a26f676bd93))

## [2.58.3](https://github.com/myparcelnl/pdk/compare/v2.58.2...v2.58.3) (2025-06-10)


### :bug: Bug Fixes

* always prefer the largest package type  in the cart ([#354](https://github.com/myparcelnl/pdk/issues/354)) ([2409df3](https://github.com/myparcelnl/pdk/commit/2409df3df86cfd0a3725b6c32fa6f5d32ad5e1ef))

## [2.58.2](https://github.com/myparcelnl/pdk/compare/v2.58.1...v2.58.2) (2025-06-06)


### :bug: Bug Fixes

* apply default origin country for ROW shipments ([#357](https://github.com/myparcelnl/pdk/issues/357)) ([ed2fdc6](https://github.com/myparcelnl/pdk/commit/ed2fdc6677fbe72120516816f595138340be5bdb))
* ensure pickup location takes precedence over 'only recipient'  ([#359](https://github.com/myparcelnl/pdk/issues/359)) ([93d9f9c](https://github.com/myparcelnl/pdk/commit/93d9f9c0f8ab832f322b10ef2cf42ece73ba0c03))
* hide same day delivery after cutoffTimeSameDay ([#356](https://github.com/myparcelnl/pdk/issues/356)) ([e4b71e9](https://github.com/myparcelnl/pdk/commit/e4b71e92ca40602fa6b838541f4243f07714e3db))

## [2.58.1](https://github.com/myparcelnl/pdk/compare/v2.58.0...v2.58.1) (2025-05-23)


### :bug: Bug Fixes

* **settings:** remove save address option when unavailable ([#355](https://github.com/myparcelnl/pdk/issues/355)) ([7af6cbb](https://github.com/myparcelnl/pdk/commit/7af6cbba1f6245f01bbc0717414de5a12edf0571))

## [2.58.0](https://github.com/myparcelnl/pdk/compare/v2.57.1...v2.58.0) (2025-05-21)


### :sparkles: New Features

* add address microservice proxy ([#338](https://github.com/myparcelnl/pdk/issues/338)) ([dd9d3b1](https://github.com/myparcelnl/pdk/commit/dd9d3b13444a3bb72a07c51becb839e020644b57))

## [2.57.1](https://github.com/myparcelnl/pdk/compare/v2.57.0...v2.57.1) (2025-05-09)


### :bug: Bug Fixes

* allow webhook delete even when delete returns resourceOwnedByOthers ([#350](https://github.com/myparcelnl/pdk/issues/350)) ([54aacde](https://github.com/myparcelnl/pdk/commit/54aacde3e2139a3304bfd481fd75018b9015460c))

## [2.57.0](https://github.com/myparcelnl/pdk/compare/v2.56.0...v2.57.0) (2025-05-06)


### :bug: Bug Fixes

* allow bpost pickups ([#351](https://github.com/myparcelnl/pdk/issues/351)) ([0e981d5](https://github.com/myparcelnl/pdk/commit/0e981d56b3c95a8fcfdf21c415fbf42a14fe9a7f))


### :sparkles: New Features

* **address:** add a setting to enable the optional address widget ([#349](https://github.com/myparcelnl/pdk/issues/349)) ([62b9919](https://github.com/myparcelnl/pdk/commit/62b9919851281c4b79f7e8812326b2d7b3c39be3))

## [2.56.0](https://github.com/myparcelnl/pdk/compare/v2.55.0...v2.56.0) (2025-05-02)


### :sparkles: New Features

* **address:** prefer split address fields, deprecate address1/2 ([#348](https://github.com/myparcelnl/pdk/issues/348)) ([4d7d17f](https://github.com/myparcelnl/pdk/commit/4d7d17ff573a702d76dc7bc0b034f52b9fe5c010)), closes [#101](https://github.com/myparcelnl/pdk/issues/101)

## [2.55.0](https://github.com/myparcelnl/pdk/compare/v2.54.1...v2.55.0) (2025-04-18)


### :sparkles: New Features

* allow receipt code be ([#326](https://github.com/myparcelnl/pdk/issues/326)) ([0d82a1b](https://github.com/myparcelnl/pdk/commit/0d82a1b51a358dfb7f74f12a76c5d87d8865ecf3))

## [2.54.1](https://github.com/myparcelnl/pdk/compare/v2.54.0...v2.54.1) (2025-04-17)


### :bug: Bug Fixes

* Prevent negative fee ([#345](https://github.com/myparcelnl/pdk/issues/345)) ([e9c213c](https://github.com/myparcelnl/pdk/commit/e9c213c688fe0d0802628de103fda42ec8488bc0))

## [2.54.0](https://github.com/myparcelnl/pdk/compare/v2.53.2...v2.54.0) (2025-04-15)


### :bug: Bug Fixes

* disable delivery date selection for Bpost carrier ([#346](https://github.com/myparcelnl/pdk/issues/346)) ([6f5ce0e](https://github.com/myparcelnl/pdk/commit/6f5ce0e560c3cdbef2a57187d089d716dc0258a7))


### :sparkles: New Features

* **address:** add pdk settings for the address widget ([#347](https://github.com/myparcelnl/pdk/issues/347)) ([d0bc2c3](https://github.com/myparcelnl/pdk/commit/d0bc2c3d14630f3dbd08f1d3bbf2b76bcddb3ee6))

## [2.53.2](https://github.com/myparcelnl/pdk/compare/v2.53.1...v2.53.2) (2025-04-09)


### :bug: Bug Fixes

* update order status on webhook ([#344](https://github.com/myparcelnl/pdk/issues/344)) ([0a7379d](https://github.com/myparcelnl/pdk/commit/0a7379da6867203a45cf18447a0f7822e127cfc3))

## [2.53.1](https://github.com/myparcelnl/pdk/compare/v2.53.0...v2.53.1) (2025-03-31)

## [2.53.0](https://github.com/myparcelnl/pdk/compare/v2.52.1...v2.53.0) (2025-03-24)


### :sparkles: New Features

* allow return shipments in PDK order collection ([#337](https://github.com/myparcelnl/pdk/issues/337)) ([bbca72b](https://github.com/myparcelnl/pdk/commit/bbca72b37239a18340701bce2a4bea3f356f17b2))

## [2.52.1](https://github.com/myparcelnl/pdk/compare/v2.52.0...v2.52.1) (2025-03-05)


### :bug: Bug Fixes

* **carrier:** fix unknown carrier ids falling back to default carrier ([#336](https://github.com/myparcelnl/pdk/issues/336)) ([dfa07e6](https://github.com/myparcelnl/pdk/commit/dfa07e6ab26fdf93b5d3e1a1083767d5b37e674b))

## [2.52.0](https://github.com/myparcelnl/pdk/compare/v2.51.2...v2.52.0) (2025-03-04)


### :sparkles: New Features

* add direct printing ([#333](https://github.com/myparcelnl/pdk/issues/333)) ([7c56e42](https://github.com/myparcelnl/pdk/commit/7c56e4285e33b9fb858991028d77da34bbebc3a0))

## [2.51.2](https://github.com/myparcelnl/pdk/compare/v2.51.1...v2.51.2) (2025-02-27)


### :bug: Bug Fixes

* **language:** return fallback translations even if language argument is specifically provided, and language is not supported ([#335](https://github.com/myparcelnl/pdk/issues/335)) ([befa2f7](https://github.com/myparcelnl/pdk/commit/befa2f7355fc6e577480deaa330f4ac6169e319d)), closes [#1252](https://github.com/myparcelnl/pdk/issues/1252)

## [2.51.1](https://github.com/myparcelnl/pdk/compare/v2.51.0...v2.51.1) (2025-02-19)


### :bug: Bug Fixes

* correctly list php 7.4 as the minimum required version ([#334](https://github.com/myparcelnl/pdk/issues/334)) ([78be974](https://github.com/myparcelnl/pdk/commit/78be9747410802ddf9b0c37a6170a2523c038c21))

## [2.51.0](https://github.com/myparcelnl/pdk/compare/v2.50.0...v2.51.0) (2025-02-05)


### :sparkles: New Features

* remember when order is auto exported ([#331](https://github.com/myparcelnl/pdk/issues/331)) ([16c6f0d](https://github.com/myparcelnl/pdk/commit/16c6f0db6ac94416084aaee9c6e94acaf0f356e2))

## [2.50.0](https://github.com/myparcelnl/pdk/compare/v2.49.1...v2.50.0) (2025-01-22)


### :sparkles: New Features

* add toggle for EU VAT and EORI fields in checkout settings ([#332](https://github.com/myparcelnl/pdk/issues/332)) ([22c9e77](https://github.com/myparcelnl/pdk/commit/22c9e77d527aa2c1928ee66f1a5a45f2eabf0dfe))
* **logging:** obfuscate authorization header in logs ([#325](https://github.com/myparcelnl/pdk/issues/325)) ([b5a0d17](https://github.com/myparcelnl/pdk/commit/b5a0d17224fb804eaf567e0525bb0f53353a4e69))

## [2.49.1](https://github.com/myparcelnl/pdk/compare/v2.49.0...v2.49.1) (2024-12-19)


### :bug: Bug Fixes

* **schema:** update PostNL BE base schema validation rules ([#329](https://github.com/myparcelnl/pdk/issues/329)) ([e2a4a5c](https://github.com/myparcelnl/pdk/commit/e2a4a5cb9db9ffca45c23b0b4e579b1ea75c38a9))

## [2.49.0](https://github.com/myparcelnl/pdk/compare/v2.48.0...v2.49.0) (2024-12-18)


### :sparkles: New Features

* add ups to nl, be and lu ([#324](https://github.com/myparcelnl/pdk/issues/324)) ([065e50f](https://github.com/myparcelnl/pdk/commit/065e50f8e87da9a56482032d99371ba19898f06a))

## [2.48.0](https://github.com/myparcelnl/pdk/compare/v2.47.2...v2.48.0) (2024-12-17)


### :sparkles: New Features

* **webhooks:** link shipment to order ([#327](https://github.com/myparcelnl/pdk/issues/327)) ([e61fc9d](https://github.com/myparcelnl/pdk/commit/e61fc9dce9d50c2b4fa990dc0eaba9aa0cd8500f))

## [2.47.2](https://github.com/myparcelnl/pdk/compare/v2.47.1...v2.47.2) (2024-11-20)


### :bug: Bug Fixes

* **validation:** remove receipt_code requirement from package to belgium ([4e099f6](https://github.com/myparcelnl/pdk/commit/4e099f6f893aef0dc015094387457ac30b6d1b51))

## [2.47.1](https://github.com/myparcelnl/pdk/compare/v2.47.0...v2.47.1) (2024-11-14)


### :bug: Bug Fixes

* **ups:** do not include delivery date for ups when exporting ([#317](https://github.com/myparcelnl/pdk/issues/317)) ([fc1eabc](https://github.com/myparcelnl/pdk/commit/fc1eabc6b7ae43963e6d66196aabae78a615148a))

## [2.47.0](https://github.com/myparcelnl/pdk/compare/v2.46.1...v2.47.0) (2024-11-13)


### :sparkles: New Features

* add receipt code ([#314](https://github.com/myparcelnl/pdk/issues/314)) ([103b856](https://github.com/myparcelnl/pdk/commit/103b8567a63fdddc30c579339951f64afae5e4c4))

## [2.46.1](https://github.com/myparcelnl/pdk/compare/v2.46.0...v2.46.1) (2024-11-08)


### :bug: Bug Fixes

* **language:** correctly use fallback language when unsupported language is used ([#315](https://github.com/myparcelnl/pdk/issues/315)) ([86952c1](https://github.com/myparcelnl/pdk/commit/86952c19ca7d5959c32cbb67a2983f3563ab058f)), closes [myparcelnl/woocommerce#1177](https://github.com/myparcelnl/woocommerce/issues/1177)

## [2.46.0](https://github.com/myparcelnl/pdk/compare/v2.45.0...v2.46.0) (2024-11-07)


### :sparkles: New Features

* allow dhlforyou pickup locations ([#316](https://github.com/myparcelnl/pdk/issues/316)) ([58ae608](https://github.com/myparcelnl/pdk/commit/58ae608f163943f52e591315a11ac41d9a318bce))

## [2.45.0](https://github.com/myparcelnl/pdk/compare/v2.44.0...v2.45.0) (2024-10-24)


### :sparkles: New Features

* improve logging ([#310](https://github.com/myparcelnl/pdk/issues/310)) ([95b0ddc](https://github.com/myparcelnl/pdk/commit/95b0ddc2caff53545f0dd1bcebacc55054057313))

## [2.44.0](https://github.com/myparcelnl/pdk/compare/v2.43.6...v2.44.0) (2024-10-24)


### :sparkles: New Features

* **debug:** add action to download logs ([#288](https://github.com/myparcelnl/pdk/issues/288)) ([a421c52](https://github.com/myparcelnl/pdk/commit/a421c526d595aa0a7de2fd41641425de012e39b2))
* **pickup:** add postnl pickup for non nl countries ([#312](https://github.com/myparcelnl/pdk/issues/312)) ([68290ae](https://github.com/myparcelnl/pdk/commit/68290ae5310769d45a6936bdc3633790cc939f03))

## [2.43.6](https://github.com/myparcelnl/pdk/compare/v2.43.5...v2.43.6) (2024-10-18)


### :bug: Bug Fixes

* resolve dpd export issue for belgium ([#311](https://github.com/myparcelnl/pdk/issues/311)) ([7a7bbdb](https://github.com/myparcelnl/pdk/commit/7a7bbdbdf7e675eb567296c3187884b58ea988b7))

## [2.43.5](https://github.com/myparcelnl/pdk/compare/v2.43.4...v2.43.5) (2024-10-09)


### :bug: Bug Fixes

* fix attributes with mutator being omitted when skipping null ([#308](https://github.com/myparcelnl/pdk/issues/308)) ([cf5d191](https://github.com/myparcelnl/pdk/commit/cf5d191e8542c9f4e53f10a6a516605bfd531b1c))

## [2.43.4](https://github.com/myparcelnl/pdk/compare/v2.43.3...v2.43.4) (2024-10-08)


### :bug: Bug Fixes

* **carriers:** remove return option for dhlforyou ([#309](https://github.com/myparcelnl/pdk/issues/309)) ([9cf0ad5](https://github.com/myparcelnl/pdk/commit/9cf0ad57d70a76ef453e0c60b60520bb7cef7b0b))

## [2.43.3](https://github.com/myparcelnl/pdk/compare/v2.43.2...v2.43.3) (2024-09-25)


### :bug: Bug Fixes

* fix deprecation warning on arrayAccess::offsetGet ([#305](https://github.com/myparcelnl/pdk/issues/305)) ([82eb15b](https://github.com/myparcelnl/pdk/commit/82eb15bd1a854340db841736f00f787813beca74))

## [2.43.2](https://github.com/myparcelnl/pdk/compare/v2.43.1...v2.43.2) (2024-09-13)


### :bug: Bug Fixes

* dpd shipment default insurance ([#304](https://github.com/myparcelnl/pdk/issues/304)) ([ac2ac72](https://github.com/myparcelnl/pdk/commit/ac2ac72eb3ac34bd03d8286ae732b3fccbee02ac))

## [2.43.1](https://github.com/myparcelnl/pdk/compare/v2.43.0...v2.43.1) (2024-09-10)


### :bug: Bug Fixes

* **checkout:** allow adding properties to checkout context settings ([#300](https://github.com/myparcelnl/pdk/issues/300)) ([3158959](https://github.com/myparcelnl/pdk/commit/3158959125492fb2485d35043e2c714152834d8b))

## [2.43.0](https://github.com/myparcelnl/pdk/compare/v2.42.3...v2.43.0) (2024-09-04)


### :sparkles: New Features

* add default pickup view setting ([#298](https://github.com/myparcelnl/pdk/issues/298)) ([7b59e0c](https://github.com/myparcelnl/pdk/commit/7b59e0cae5e039bf5f921215f7e395f55f840702))

## [2.42.3](https://github.com/myparcelnl/pdk/compare/v2.42.2...v2.42.3) (2024-09-03)


### :bug: Bug Fixes

* **export:** allow dpd pickup ([#299](https://github.com/myparcelnl/pdk/issues/299)) ([f609749](https://github.com/myparcelnl/pdk/commit/f609749d47b2328ed508a713c97828023cb0dec2))

## [2.42.2](https://github.com/myparcelnl/pdk/compare/v2.42.1...v2.42.2) (2024-08-30)


### :bug: Bug Fixes

* **export:** allow dpd shipments ([#296](https://github.com/myparcelnl/pdk/issues/296)) ([8efb772](https://github.com/myparcelnl/pdk/commit/8efb7724b57b588cc601140639e8eb8f6ae1faa9))

## [2.42.1](https://github.com/myparcelnl/pdk/compare/v2.42.0...v2.42.1) (2024-08-15)


### :bug: Bug Fixes

* **carriers:** fix errors in another postnl carrier configuration ([#292](https://github.com/myparcelnl/pdk/issues/292)) ([9996991](https://github.com/myparcelnl/pdk/commit/9996991f68aa598624709620d1e60d0dbe649571))
* **webhooks:** always refresh base url on regeneration of webhooks ([#293](https://github.com/myparcelnl/pdk/issues/293)) ([eb72819](https://github.com/myparcelnl/pdk/commit/eb72819b05b7b567d02b63f3ccceca0b9fc62ecf))

## [2.42.0](https://github.com/myparcelnl/pdk/compare/v2.41.0...v2.42.0) (2024-08-05)


### :sparkles: New Features

* add options for small package Belgium ([#290](https://github.com/myparcelnl/pdk/issues/290)) ([1af1dac](https://github.com/myparcelnl/pdk/commit/1af1dac8c7950a75c77daf1119af38e000c7182b))
* add support for DPD BBP ([#291](https://github.com/myparcelnl/pdk/issues/291)) ([f97b148](https://github.com/myparcelnl/pdk/commit/f97b1481f72cbf2a9a2b61aa8c97225bb31751a2))

## [2.41.0](https://github.com/myparcelnl/pdk/compare/v2.40.0...v2.41.0) (2024-07-29)


### :sparkles: New Features

* add international mailbox support when shipping to belgium ([#289](https://github.com/myparcelnl/pdk/issues/289)) ([ef5c714](https://github.com/myparcelnl/pdk/commit/ef5c714a38247d7bdbee5164867a399162ff73b9))

## [2.40.0](https://github.com/myparcelnl/pdk/compare/v2.39.3...v2.40.0) (2024-07-17)


### :sparkles: New Features

* add international mailbox for carriers with custom contract ([#279](https://github.com/myparcelnl/pdk/issues/279)) ([468ed0f](https://github.com/myparcelnl/pdk/commit/468ed0f9f62b1a780c6c45203eeda36a3d4c13b5))
* **model:** remove throwing cast errors from models ([#286](https://github.com/myparcelnl/pdk/issues/286)) ([5f0ac54](https://github.com/myparcelnl/pdk/commit/5f0ac548d3ca096b50298c6b9f2cd1e1b8e5d818))

## [2.39.3](https://github.com/myparcelnl/pdk/compare/v2.39.2...v2.39.3) (2024-07-12)


### :bug: Bug Fixes

* fix duplicate carriers when custom postnl contract is enabled ([#287](https://github.com/myparcelnl/pdk/issues/287)) ([ab78bf3](https://github.com/myparcelnl/pdk/commit/ab78bf3700b2ba8236e6dcdf0ae00dff9ac7cd0a)), closes [#284](https://github.com/myparcelnl/pdk/issues/284)

## [2.39.2](https://github.com/myparcelnl/pdk/compare/v2.39.1...v2.39.2) (2024-07-10)


### :bug: Bug Fixes

* **validation:** fix error caused by tracked property ([#280](https://github.com/myparcelnl/pdk/issues/280)) ([0b73fef](https://github.com/myparcelnl/pdk/commit/0b73fef881a850244d0944656ddb54836ae3cb6e))

## [2.39.1](https://github.com/myparcelnl/pdk/compare/v2.39.0...v2.39.1) (2024-07-05)


### :bug: Bug Fixes

* fix max insurance setting not being enforced ([#283](https://github.com/myparcelnl/pdk/issues/283)) ([86a6b83](https://github.com/myparcelnl/pdk/commit/86a6b83b5f14bca64382f00373360676ce24939b))

## [2.39.0](https://github.com/myparcelnl/pdk/compare/v2.38.1...v2.39.0) (2024-07-04)


### :sparkles: New Features

* **settings:** allow showing description with shipping method ([#285](https://github.com/myparcelnl/pdk/issues/285)) ([2a80eca](https://github.com/myparcelnl/pdk/commit/2a80ecabb52bcaba2430151a077c3c2476c30942))

## [2.38.1](https://github.com/myparcelnl/pdk/compare/v2.38.0...v2.38.1) (2024-06-26)


### :bug: Bug Fixes

* **validation:** improve validation for mailbox and letter ([#277](https://github.com/myparcelnl/pdk/issues/277)) ([6df536b](https://github.com/myparcelnl/pdk/commit/6df536b79569ada3d10fb16eef0e46ed4c68ae59))

## [2.38.0](https://github.com/myparcelnl/pdk/compare/v2.37.1...v2.38.0) (2024-06-21)


### :sparkles: New Features

* add default for bbp ([#282](https://github.com/myparcelnl/pdk/issues/282)) ([29e238f](https://github.com/myparcelnl/pdk/commit/29e238fd8c7c69725b956df38bfd857733171d92))

## [2.37.1](https://github.com/myparcelnl/pdk/compare/v2.37.0...v2.37.1) (2024-06-13)


### :bug: Bug Fixes

* preserve address while calculating shipping method ([#276](https://github.com/myparcelnl/pdk/issues/276)) ([f83b25f](https://github.com/myparcelnl/pdk/commit/f83b25fcdd7eb3270c3fa89b7663d75cd94a905b))

## [2.37.0](https://github.com/myparcelnl/pdk/compare/v2.36.5...v2.37.0) (2024-06-07)


### :sparkles: New Features

* **settings:** add shipping methods input ([#275](https://github.com/myparcelnl/pdk/issues/275)) ([e2d75a6](https://github.com/myparcelnl/pdk/commit/e2d75a6c3f6655f7dc9d4b8570a34eb794483356))

## [2.36.5](https://github.com/myparcelnl/pdk/compare/v2.36.4...v2.36.5) (2024-05-24)


### :bug: Bug Fixes

* **model:** fix nested array keys case being changed ([#274](https://github.com/myparcelnl/pdk/issues/274)) ([3f93742](https://github.com/myparcelnl/pdk/commit/3f9374232691180248caa2cc548c8d74e7e89a80))

## [2.36.4](https://github.com/myparcelnl/pdk/compare/v2.36.3...v2.36.4) (2024-05-22)


### :bug: Bug Fixes

* allow string representation of default setting for label ([#271](https://github.com/myparcelnl/pdk/issues/271)) ([7c92a58](https://github.com/myparcelnl/pdk/commit/7c92a588bbba9ab9fa1b7858d4ae805381f585be))
* **checkout:** consider empty package weight ([#273](https://github.com/myparcelnl/pdk/issues/273)) ([7acc786](https://github.com/myparcelnl/pdk/commit/7acc7868e07bb9ab3828a244b942eabb8e0b3d2f))

## [2.36.3](https://github.com/myparcelnl/pdk/compare/v2.36.2...v2.36.3) (2024-05-01)


### :bug: Bug Fixes

* **checkout:** honor product variant settings ([#269](https://github.com/myparcelnl/pdk/issues/269)) ([524689f](https://github.com/myparcelnl/pdk/commit/524689f77aedbfd188a26f4a677098ebb92a8efa))

## [2.36.2](https://github.com/myparcelnl/pdk/compare/v2.36.1...v2.36.2) (2024-04-22)


### :bug: Bug Fixes

* **insurance:** fix belgium insurance possibilities ([#267](https://github.com/myparcelnl/pdk/issues/267)) ([f577424](https://github.com/myparcelnl/pdk/commit/f57742490f6056b5ceed7a99ebb99153f97a7f21))

## [2.36.1](https://github.com/myparcelnl/pdk/compare/v2.36.0...v2.36.1) (2024-04-16)


### :bug: Bug Fixes

* **checkout:** fix error when loading delivery options strings ([#268](https://github.com/myparcelnl/pdk/issues/268)) ([d56f2e4](https://github.com/myparcelnl/pdk/commit/d56f2e48c37d14efe7bb3f6294c311a44243dceb))

## [2.36.0](https://github.com/myparcelnl/pdk/compare/v2.35.0...v2.36.0) (2024-04-12)


### :sparkles: New Features

* include delivery options translations in context ([#266](https://github.com/myparcelnl/pdk/issues/266)) ([af2ed16](https://github.com/myparcelnl/pdk/commit/af2ed1648bbff5d74d9d4e8eb5e95ec3b47c5bcc))

## [2.35.0](https://github.com/myparcelnl/pdk/compare/v2.34.0...v2.35.0) (2024-04-04)


### :sparkles: New Features

* update to latest delivery options ([#263](https://github.com/myparcelnl/pdk/issues/263)) ([88f0c70](https://github.com/myparcelnl/pdk/commit/88f0c70026b2e24fc580008e4d5389fcf55fca98))

## [2.34.0](https://github.com/myparcelnl/pdk/compare/v2.33.2...v2.34.0) (2024-03-21)


### :sparkles: New Features

* **installer:** allow differentiating between migration types ([#261](https://github.com/myparcelnl/pdk/issues/261)) ([21ce3cf](https://github.com/myparcelnl/pdk/commit/21ce3cf71eba3fbad82682c913e9846b21789f41))


### :bug: Bug Fixes

* **orders:** fix customs declaration items error in row countries ([#262](https://github.com/myparcelnl/pdk/issues/262)) ([d22d491](https://github.com/myparcelnl/pdk/commit/d22d4911203e628f5a29555d2eb8468b6550c2fb))

## [2.33.2](https://github.com/myparcelnl/pdk/compare/v2.33.1...v2.33.2) (2024-03-13)


### :bug: Bug Fixes

* fix validation schema for package small ([#260](https://github.com/myparcelnl/pdk/issues/260)) ([bb6ad73](https://github.com/myparcelnl/pdk/commit/bb6ad73e9abbd2254d0d52abfd4f2a405cc6d24e))

## [2.33.1](https://github.com/myparcelnl/pdk/compare/v2.33.0...v2.33.1) (2024-03-12)


### :bug: Bug Fixes

* disable tracked for nl small packages ([#259](https://github.com/myparcelnl/pdk/issues/259)) ([e07bf35](https://github.com/myparcelnl/pdk/commit/e07bf35ab35c668875379226cd46aa2fc90c7ae5))

## [2.33.0](https://github.com/myparcelnl/pdk/compare/v2.32.2...v2.33.0) (2024-03-05)


### :sparkles: New Features

* add package type package small ([#249](https://github.com/myparcelnl/pdk/issues/249)) ([37c6dc8](https://github.com/myparcelnl/pdk/commit/37c6dc817758880825b7ab95729a77583ff0a257))

## [2.32.2](https://github.com/myparcelnl/pdk/compare/v2.32.1...v2.32.2) (2024-02-28)


### :bug: Bug Fixes

* **carriers:** use correct subscription id ([#257](https://github.com/myparcelnl/pdk/issues/257)) ([3662c06](https://github.com/myparcelnl/pdk/commit/3662c067854c55b501c5cadaa1015242aa7593c6))
* **logging:** log successful response bodies ([#256](https://github.com/myparcelnl/pdk/issues/256)) ([32e0f3c](https://github.com/myparcelnl/pdk/commit/32e0f3cfcafce53e73628ad5157b19dfca65e5d2))

## [2.32.1](https://github.com/myparcelnl/pdk/compare/v2.32.0...v2.32.1) (2024-02-15)


### :bug: Bug Fixes

* **calculator:** disable shipment options for postnl pickup ([#252](https://github.com/myparcelnl/pdk/issues/252)) ([3d66e83](https://github.com/myparcelnl/pdk/commit/3d66e834f6741a9697e457a66912dd5cf0eccb91))
* **orders:** fix contract ids being ignored on export ([#253](https://github.com/myparcelnl/pdk/issues/253)) ([29e4bda](https://github.com/myparcelnl/pdk/commit/29e4bda2f7456b1e6821bc05842046d1e126abe4))

## [2.32.0](https://github.com/myparcelnl/pdk/compare/v2.31.5...v2.32.0) (2024-01-02)


### :sparkles: New Features

* **config:** add new dpz range ([#243](https://github.com/myparcelnl/pdk/issues/243)) ([e72f31e](https://github.com/myparcelnl/pdk/commit/e72f31e8fb78eb8f61a12bce8594a50c2fb4c64c))

## [2.31.5](https://github.com/myparcelnl/pdk/compare/v2.31.4...v2.31.5) (2023-12-13)


### :bug: Bug Fixes

* **export:** fix multiple export of same order ([#244](https://github.com/myparcelnl/pdk/issues/244)) ([fda39cc](https://github.com/myparcelnl/pdk/commit/fda39cc4976f63b6416640066cae30bedc2048bc))

## [2.31.4](https://github.com/myparcelnl/pdk/compare/v2.31.3...v2.31.4) (2023-12-04)


### :bug: Bug Fixes

* **settings:** disallow "none" in customs country of origin ([#241](https://github.com/myparcelnl/pdk/issues/241)) ([6376df6](https://github.com/myparcelnl/pdk/commit/6376df65aee339f1d11bba3e93d866df7e3ec392))

## [2.31.3](https://github.com/myparcelnl/pdk/compare/v2.31.2...v2.31.3) (2023-11-27)


### :bug: Bug Fixes

* **core:** connect audit service ([13150b2](https://github.com/myparcelnl/pdk/commit/13150b2e1e6fc292432f90e3f6807e572320f287))
* re-add deprecated class ([10130eb](https://github.com/myparcelnl/pdk/commit/10130eb063f33593d7be54527980e5a77fe71099))

## [2.31.2](https://github.com/myparcelnl/pdk/compare/v2.31.1...v2.31.2) (2023-11-27)


### :bug: Bug Fixes

* **core:** correct names of interfaces ([d4a08c0](https://github.com/myparcelnl/pdk/commit/d4a08c035a1dd5ccec5ebb4152ca098a7f03fcd6))

## [2.31.1](https://github.com/myparcelnl/pdk/compare/v2.31.0...v2.31.1) (2023-11-23)


### :bug: Bug Fixes

* **caching:** use apc only when enabled ([#239](https://github.com/myparcelnl/pdk/issues/239)) ([7b4ebbd](https://github.com/myparcelnl/pdk/commit/7b4ebbdfcc2563665355e70dabb00b0e46024f90))

## [2.31.0](https://github.com/myparcelnl/pdk/compare/v2.30.4...v2.31.0) (2023-11-22)


### :sparkles: New Features

* add audits ([#236](https://github.com/myparcelnl/pdk/issues/236)) ([646e6b9](https://github.com/myparcelnl/pdk/commit/646e6b95582708bb4a5a332465bdfca0c6fcda79))

## [2.30.4](https://github.com/myparcelnl/pdk/compare/v2.30.3...v2.30.4) (2023-11-14)


### :bug: Bug Fixes

* **base:** fix error when calling toArray with empty values ([#238](https://github.com/myparcelnl/pdk/issues/238)) ([a5975d1](https://github.com/myparcelnl/pdk/commit/a5975d16f38d20ec1cf44874e47dfef4eab67431))
* **calculator:** always send insurance for dpd ([#219](https://github.com/myparcelnl/pdk/issues/219)) ([a360ae8](https://github.com/myparcelnl/pdk/commit/a360ae8151cc4fb0aac8ece9df97c14ea05efbd7))

## [2.30.3](https://github.com/myparcelnl/pdk/compare/v2.30.2...v2.30.3) (2023-11-13)


### :bug: Bug Fixes

* **orders:** fix notice when billing address or recipient are not present ([#237](https://github.com/myparcelnl/pdk/issues/237)) ([715d6f4](https://github.com/myparcelnl/pdk/commit/715d6f41b96ab6184de127ad5243a33a494b3386))

## [2.30.2](https://github.com/myparcelnl/pdk/compare/v2.30.1...v2.30.2) (2023-11-09)


### :bug: Bug Fixes

* **carriers:** fix carriers defaulting to disabled ([#235](https://github.com/myparcelnl/pdk/issues/235)) ([965c6d7](https://github.com/myparcelnl/pdk/commit/965c6d70986efa31d6b58c662c4e8c083ebc697a))

## [2.30.1](https://github.com/myparcelnl/pdk/compare/v2.30.0...v2.30.1) (2023-11-08)


### :bug: Bug Fixes

* **webhooks:** fix order webhooks ([#225](https://github.com/myparcelnl/pdk/issues/225)) ([abe4a46](https://github.com/myparcelnl/pdk/commit/abe4a465be2f20677b4f17943f8b02d2bc69f4a8))

## [2.30.0](https://github.com/myparcelnl/pdk/compare/v2.29.0...v2.30.0) (2023-11-07)


### :sparkles: New Features

* **shipments:** add multicollo ([#229](https://github.com/myparcelnl/pdk/issues/229)) ([63b25f3](https://github.com/myparcelnl/pdk/commit/63b25f36cb31b246a0f1c585ea644cdb4f714789))

## [2.29.0](https://github.com/myparcelnl/pdk/compare/v2.28.9...v2.29.0) (2023-11-07)


### :sparkles: New Features

* **orders:** generate customs declaration automatically ([#232](https://github.com/myparcelnl/pdk/issues/232)) ([cd3e7e1](https://github.com/myparcelnl/pdk/commit/cd3e7e1768651a428f1ae6a07b235677109a8894))


### :bug: Bug Fixes

* **orders:** fix exporting shipments occasionally causing visual error ([#234](https://github.com/myparcelnl/pdk/issues/234)) ([1625978](https://github.com/myparcelnl/pdk/commit/16259785b747fdbae4392117f24afc2ca8cfe327))

## [2.28.9](https://github.com/myparcelnl/pdk/compare/v2.28.8...v2.28.9) (2023-11-06)


### :bug: Bug Fixes

* **model:** fix error when unsetting property using skip_null ([e6bf984](https://github.com/myparcelnl/pdk/commit/e6bf984b76ef3019928356e9f0d7525e650fd8de))

## [2.28.8](https://github.com/myparcelnl/pdk/compare/v2.28.7...v2.28.8) (2023-11-06)


### :bug: Bug Fixes

* **account:** fix carriers being disabled when getting account ([#231](https://github.com/myparcelnl/pdk/issues/231)) ([d5210b1](https://github.com/myparcelnl/pdk/commit/d5210b115c5e2c9e69e09cab0f18a98de3f50ae1))

## [2.28.7](https://github.com/myparcelnl/pdk/compare/v2.28.6...v2.28.7) (2023-11-03)


### :bug: Bug Fixes

* **base:** make null flag in toArray act more consistently ([#230](https://github.com/myparcelnl/pdk/issues/230)) ([a7177f8](https://github.com/myparcelnl/pdk/commit/a7177f82af81c0e61385a05406a4f1aaf344ded7))

## [2.28.6](https://github.com/myparcelnl/pdk/compare/v2.28.5...v2.28.6) (2023-10-27)


### :bug: Bug Fixes

* **core:** fix data that should not be stored being stored ([#226](https://github.com/myparcelnl/pdk/issues/226)) ([77e8c09](https://github.com/myparcelnl/pdk/commit/77e8c09a1a9e3a3f1b8ebd915ed09e8c605e2dda))

## [2.28.5](https://github.com/myparcelnl/pdk/compare/v2.28.4...v2.28.5) (2023-10-26)


### :bug: Bug Fixes

* **order:** fix error on updating order status if setting is empty ([#221](https://github.com/myparcelnl/pdk/issues/221)) ([b36d55c](https://github.com/myparcelnl/pdk/commit/b36d55c42b4b71208148d1f26342db1ca5f993a4))
* **support:** make toArray act consistently everywhere ([#224](https://github.com/myparcelnl/pdk/issues/224)) ([5f0d5f2](https://github.com/myparcelnl/pdk/commit/5f0d5f2795a418941b9e9c96ba53f6ccf2f1615e))

## [2.28.4](https://github.com/myparcelnl/pdk/compare/v2.28.3...v2.28.4) (2023-10-25)


### :bug: Bug Fixes

* **carriers:** always pass customer info for dpd ([#220](https://github.com/myparcelnl/pdk/issues/220)) ([a5ece07](https://github.com/myparcelnl/pdk/commit/a5ece0777da8c1b5429d83488d8525537cc4d7be))
* **language:** fix error when language is absent ([#222](https://github.com/myparcelnl/pdk/issues/222)) ([0dd4bb0](https://github.com/myparcelnl/pdk/commit/0dd4bb0e725fc7c9d6de7bd250d6c0dbb06fbec2))

## [2.28.3](https://github.com/myparcelnl/pdk/compare/v2.28.2...v2.28.3) (2023-10-24)


### :bug: Bug Fixes

* **carriers:** update carrier capabilities ([#218](https://github.com/myparcelnl/pdk/issues/218)) ([8cab5f8](https://github.com/myparcelnl/pdk/commit/8cab5f8b1fafd98e5a90c2fd44772e8c4740e541))

## [2.28.2](https://github.com/myparcelnl/pdk/compare/v2.28.1...v2.28.2) (2023-10-24)


### :bug: Bug Fixes

* **deps:** add psr/log to prod dependencies ([f648448](https://github.com/myparcelnl/pdk/commit/f6484485257e83870c41dc3b399ac93f7fcd6461))

## [2.28.1](https://github.com/myparcelnl/pdk/compare/v2.28.0...v2.28.1) (2023-10-20)


### :bug: Bug Fixes

* **returns:** prevent validation error on export return ([#216](https://github.com/myparcelnl/pdk/issues/216)) ([a046143](https://github.com/myparcelnl/pdk/commit/a046143b65931d1f00c982dd96858810440caf42))

## [2.28.0](https://github.com/myparcelnl/pdk/compare/v2.27.0...v2.28.0) (2023-10-13)


### :bug: Bug Fixes

* **shipment:** include address2 in street ([#214](https://github.com/myparcelnl/pdk/issues/214)) ([0e434de](https://github.com/myparcelnl/pdk/commit/0e434de78c0df1ee3d10fccfe288ddc77a4f4ed7))


### :sparkles: New Features

* **order:** allow changing digital stamp weight range ([#215](https://github.com/myparcelnl/pdk/issues/215)) ([20a58fa](https://github.com/myparcelnl/pdk/commit/20a58fa7d4a69ef4faec0bda5db5fce2d05a2181))

## [2.27.0](https://github.com/myparcelnl/pdk/compare/v2.26.0...v2.27.0) (2023-10-11)


### :sparkles: New Features

* **carriers:** add dpd for platform myparcel ([#211](https://github.com/myparcelnl/pdk/issues/211)) ([8928bdd](https://github.com/myparcelnl/pdk/commit/8928bdde7fdf72155c85995f785a2eac9b85c794))

## [2.26.0](https://github.com/myparcelnl/pdk/compare/v2.25.2...v2.26.0) (2023-10-04)


### :sparkles: New Features

* **orders:** add order number property ([#210](https://github.com/myparcelnl/pdk/issues/210)) ([0106cd7](https://github.com/myparcelnl/pdk/commit/0106cd75ba37974aaf7ae2fa7290c6ef9141538c))

## [2.25.2](https://github.com/myparcelnl/pdk/compare/v2.25.1...v2.25.2) (2023-09-29)


### :bug: Bug Fixes

* **orders:** limit label description to max length ([#190](https://github.com/myparcelnl/pdk/issues/190)) ([0adca88](https://github.com/myparcelnl/pdk/commit/0adca883da0d427f447f00890a2b5cc7fc49fd2f))

## [2.25.1](https://github.com/myparcelnl/pdk/compare/v2.25.0...v2.25.1) (2023-09-29)


### :bug: Bug Fixes

* **returns:** fix faulty shipment options ([#208](https://github.com/myparcelnl/pdk/issues/208)) ([e754b05](https://github.com/myparcelnl/pdk/commit/e754b05b98c86fd93785b5117d0694ff6ab14b7e))
* **schema:** fix europlus validation errors ([#209](https://github.com/myparcelnl/pdk/issues/209)) ([13712ff](https://github.com/myparcelnl/pdk/commit/13712ffd2f920ddea123eed5137cb0be10e188fd))

## [2.25.0](https://github.com/myparcelnl/pdk/compare/v2.24.2...v2.25.0) (2023-09-28)


### :sparkles: New Features

* **calculator:** add empty package weight ([#207](https://github.com/myparcelnl/pdk/issues/207)) ([b125005](https://github.com/myparcelnl/pdk/commit/b125005495a1719c046cd8ba36344c57778cdc31))


### :bug: Bug Fixes

* **calculator:** ensure signature for dhl europlus and parcelconnect orders ([#206](https://github.com/myparcelnl/pdk/issues/206)) ([1c542c9](https://github.com/myparcelnl/pdk/commit/1c542c9430c377f39500cbee17a6fe9fa9a65bb1))

## [2.24.2](https://github.com/myparcelnl/pdk/compare/v2.24.1...v2.24.2) (2023-09-27)


### :bug: Bug Fixes

* **settings:** fix label description not inheriting from defaults ([#201](https://github.com/myparcelnl/pdk/issues/201)) ([d5aae9e](https://github.com/myparcelnl/pdk/commit/d5aae9e58dbceca5864a6058e9797472d88893e1))
* **shipments:** inherit delivery date from order ([#200](https://github.com/myparcelnl/pdk/issues/200)) ([355e71f](https://github.com/myparcelnl/pdk/commit/355e71f6ad1fa145e56824ad83d2dd310c12ddbc))
* **validation:** remove required date for dhl shipments ([#205](https://github.com/myparcelnl/pdk/issues/205)) ([ef9d323](https://github.com/myparcelnl/pdk/commit/ef9d323066e843b637b5f073f6f7e52d9809166b))

## [2.24.1](https://github.com/myparcelnl/pdk/compare/v2.24.0...v2.24.1) (2023-09-26)


### :bug: Bug Fixes

* **orders:** ensure signature for postnl pickup ([#204](https://github.com/myparcelnl/pdk/issues/204)) ([c40b0ae](https://github.com/myparcelnl/pdk/commit/c40b0ae98d418266845c6f0be3343707b53374a9))

## [2.24.0](https://github.com/myparcelnl/pdk/compare/v2.23.1...v2.24.0) (2023-09-25)


### :sparkles: New Features

* **carriers:** add ups ([#202](https://github.com/myparcelnl/pdk/issues/202)) ([ecbbb2e](https://github.com/myparcelnl/pdk/commit/ecbbb2e228fe7b74fcdfdbc30b1c49ff8c0ae6f9))

## [2.23.1](https://github.com/myparcelnl/pdk/compare/v2.23.0...v2.23.1) (2023-09-22)


### :bug: Bug Fixes

* **notifications:** pass notifications to frontend ([#199](https://github.com/myparcelnl/pdk/issues/199)) ([da5e730](https://github.com/myparcelnl/pdk/commit/da5e73016bc3cdc3ddc6d9a687a99ea9e703f823))

## [2.23.0](https://github.com/myparcelnl/pdk/compare/v2.22.0...v2.23.0) (2023-09-19)


### :sparkles: New Features

* **orders:** change status after creating label ([#196](https://github.com/myparcelnl/pdk/issues/196)) ([10ae5d2](https://github.com/myparcelnl/pdk/commit/10ae5d220380984fcd9ad220929684fe1641b0bf))


### :bug: Bug Fixes

* **print-action:** cast order ids to array ([#198](https://github.com/myparcelnl/pdk/issues/198)) ([3ac559c](https://github.com/myparcelnl/pdk/commit/3ac559cf12b6b723128ea0b35365a5ee51e7252d))

## [2.22.0](https://github.com/myparcelnl/pdk/compare/v2.21.0...v2.22.0) (2023-09-18)


### :bug: Bug Fixes

* **settings:** set country of origin to local country by default ([#195](https://github.com/myparcelnl/pdk/issues/195)) ([dd81b6d](https://github.com/myparcelnl/pdk/commit/dd81b6d4bf6a1424d853ff67f6dace37a6d5bb44))


### :sparkles: New Features

* **config:** add bulk actions definitions ([#197](https://github.com/myparcelnl/pdk/issues/197)) ([02d8f97](https://github.com/myparcelnl/pdk/commit/02d8f97cfae990bccd7140a0c6c9b0d9f96f5f1c))

## [2.21.0](https://github.com/myparcelnl/pdk/compare/v2.20.1...v2.21.0) (2023-09-15)


### :sparkles: New Features

* **settings:** add sort prop to order status fields ([#194](https://github.com/myparcelnl/pdk/issues/194)) ([144d6c9](https://github.com/myparcelnl/pdk/commit/144d6c90446b711d46df80a388fb92d533d43e05))


### :bug: Bug Fixes

* **settings:** hide disabled settings on all form views ([#189](https://github.com/myparcelnl/pdk/issues/189)) ([84429ab](https://github.com/myparcelnl/pdk/commit/84429ab8d1a7864dc89061bec6e7f7515ceb24d0))
* **shipments:** allow exporting eu and row shipments ([#186](https://github.com/myparcelnl/pdk/issues/186)) ([41b1bef](https://github.com/myparcelnl/pdk/commit/41b1bef73220338b2f27622cb5ce19908fe03b31))

## [2.20.1](https://github.com/myparcelnl/pdk/compare/v2.20.0...v2.20.1) (2023-09-13)


### :bug: Bug Fixes

* **carriers:** fix non-platform carriers being turned into postnl ([#188](https://github.com/myparcelnl/pdk/issues/188)) ([dc4d402](https://github.com/myparcelnl/pdk/commit/dc4d40244d96e5146ad32622fc0ec89c9739297c))

## [2.20.0](https://github.com/myparcelnl/pdk/compare/v2.19.2...v2.20.0) (2023-09-12)


### :bug: Bug Fixes

* **delivery-options:** add drop off days to config ([#185](https://github.com/myparcelnl/pdk/issues/185)) ([47c8d7e](https://github.com/myparcelnl/pdk/commit/47c8d7e13268a858f585bcac637f9465d3c1c9c2))


### :sparkles: New Features

* **settings:** allow hiding settings via config ([#187](https://github.com/myparcelnl/pdk/issues/187)) ([7fc2d0b](https://github.com/myparcelnl/pdk/commit/7fc2d0b920b9df2e1c5a013a91e30793d9f0f3b1))

## [2.19.2](https://github.com/myparcelnl/pdk/compare/v2.19.1...v2.19.2) (2023-09-08)


### :bug: Bug Fixes

* **delivery-options:** fix price surcharge ([#171](https://github.com/myparcelnl/pdk/issues/171)) ([f1b8e11](https://github.com/myparcelnl/pdk/commit/f1b8e1124789110f97ead79fbfb9204638e36035))
* **frontend:** fix options label and value when passed as associative array ([#182](https://github.com/myparcelnl/pdk/issues/182)) ([29d0fd3](https://github.com/myparcelnl/pdk/commit/29d0fd3199f41bc6dd4311cb7df3d9d9223cb24d))
* **product-settings:** allow default drop off delay value ([#183](https://github.com/myparcelnl/pdk/issues/183)) ([6c9d27e](https://github.com/myparcelnl/pdk/commit/6c9d27ea617281f5d1d53b5a4116f563dad8a798))
* **settings:** change insurance factor to percentage ([#179](https://github.com/myparcelnl/pdk/issues/179)) ([83a6edd](https://github.com/myparcelnl/pdk/commit/83a6eddff45127c3bef80c81912892bd9f5d5aa4))
* **settings:** move general settings to order settings ([#184](https://github.com/myparcelnl/pdk/issues/184)) ([4faf0af](https://github.com/myparcelnl/pdk/commit/4faf0af63e515c888e37f37c141de5bd2fb5c31e))
* **settings:** sort settings options ([#180](https://github.com/myparcelnl/pdk/issues/180)) ([1512b23](https://github.com/myparcelnl/pdk/commit/1512b238cb073e2dd4224de790fc8d5c0faa21f2))
* **shipments:** correctly pass physical properties ([#181](https://github.com/myparcelnl/pdk/issues/181)) ([f2564fe](https://github.com/myparcelnl/pdk/commit/f2564fe92b2a6399606d5c0b2674fe52b9a6ba76))

## [2.19.1](https://github.com/myparcelnl/pdk/compare/v2.19.0...v2.19.1) (2023-09-07)


### :bug: Bug Fixes

* **carriers:** distinguish carrier capabilities by platform ([#178](https://github.com/myparcelnl/pdk/issues/178)) ([619c9da](https://github.com/myparcelnl/pdk/commit/619c9da8256e9fa5b6f5b1a0c67025bcbc5d735e))
* **settings:** move order status mail and notification to general ([#175](https://github.com/myparcelnl/pdk/issues/175)) ([a5725c2](https://github.com/myparcelnl/pdk/commit/a5725c223c048dfb96810a1013019ea8d34d27ce))

## [2.19.0](https://github.com/myparcelnl/pdk/compare/v2.18.0...v2.19.0) (2023-09-04)


### :sparkles: New Features

* **account:** add action to update subscription features ([#133](https://github.com/myparcelnl/pdk/issues/133)) ([1c856ba](https://github.com/myparcelnl/pdk/commit/1c856ba505531d43638c6c58ea212f6a6bb32b0e))
* **orders:** improve order validation ([#169](https://github.com/myparcelnl/pdk/issues/169)) ([694437b](https://github.com/myparcelnl/pdk/commit/694437bcc12ca192035b4350f789aba51f6d035a))

## [2.18.0](https://github.com/myparcelnl/pdk/compare/v2.17.1...v2.18.0) (2023-08-31)


### :sparkles: New Features

* **product-settings:** add fit in digital stamp ([#170](https://github.com/myparcelnl/pdk/issues/170)) ([8040855](https://github.com/myparcelnl/pdk/commit/8040855954b2ac3e4aa358923c72f508d7bb80a0))
* **settings:** implement send return email setting ([#153](https://github.com/myparcelnl/pdk/issues/153)) ([52ce7e1](https://github.com/myparcelnl/pdk/commit/52ce7e1e2fa5af15c48882b5f20bc6eeaa831355))

## [2.17.1](https://github.com/myparcelnl/pdk/compare/v2.17.0...v2.17.1) (2023-08-30)


### :bug: Bug Fixes

* **shipments:** prevent error in barcode note identifier ([#168](https://github.com/myparcelnl/pdk/issues/168)) ([f5f5551](https://github.com/myparcelnl/pdk/commit/f5f5551a2fde6e669a980d38c4b85105440d9442))

## [2.17.0](https://github.com/myparcelnl/pdk/compare/v2.16.2...v2.17.0) (2023-08-30)


### :sparkles: New Features

* **form-builder:** add more builder operations ([#165](https://github.com/myparcelnl/pdk/issues/165)) ([0132e01](https://github.com/myparcelnl/pdk/commit/0132e01b20a81d625c6e502f9cc95ab8379882c1))
* **shipments:** save barcode in order note ([#155](https://github.com/myparcelnl/pdk/issues/155)) ([2c74dbf](https://github.com/myparcelnl/pdk/commit/2c74dbfa71f4091146ca5f88eee5d48ec065f950))


### :bug: Bug Fixes

* **shipments:** fix non-concept shipments not returning barcode instantly ([#163](https://github.com/myparcelnl/pdk/issues/163)) ([b7726a4](https://github.com/myparcelnl/pdk/commit/b7726a45791ffef5e975fb6e9634262514a9557d))

## [2.16.2](https://github.com/myparcelnl/pdk/compare/v2.16.1...v2.16.2) (2023-08-24)


### :bug: Bug Fixes

* **form:** treat value strings that are also callable as a string ([#162](https://github.com/myparcelnl/pdk/issues/162)) ([ef5fa5e](https://github.com/myparcelnl/pdk/commit/ef5fa5ef1f981077bf32dfb99973539288697a30))

## [2.16.1](https://github.com/myparcelnl/pdk/compare/v2.16.0...v2.16.1) (2023-08-16)


### :bug: Bug Fixes

* **container:** add default implementation for file system interface ([ea00035](https://github.com/myparcelnl/pdk/commit/ea00035942198caffe92e0acc8373732cd52640b))

## [2.16.0](https://github.com/myparcelnl/pdk/compare/v2.15.0...v2.16.0) (2023-08-15)


### :bug: Bug Fixes

* **orders:** fix order notes not being posted ([#157](https://github.com/myparcelnl/pdk/issues/157)) ([b0c1d7b](https://github.com/myparcelnl/pdk/commit/b0c1d7b038452d3975b60cb4aac4e56fa69ee408))


### :sparkles: New Features

* add file system interface ([#61](https://github.com/myparcelnl/pdk/issues/61)) ([eb4700b](https://github.com/myparcelnl/pdk/commit/eb4700b466596c89c19d7389e23cdf3afbaaf80e))

## [2.15.0](https://github.com/myparcelnl/pdk/compare/v2.14.1...v2.15.0) (2023-08-11)


### :sparkles: New Features

* **settings:** allow embedding more interactivity in form views ([#156](https://github.com/myparcelnl/pdk/issues/156)) ([b4dcd30](https://github.com/myparcelnl/pdk/commit/b4dcd3087bd8aeb0a842ddaf55f17c948c9f71ac))


### :bug: Bug Fixes

* **carriers:** sort in preferred order for displaying names ([#152](https://github.com/myparcelnl/pdk/issues/152)) ([060867c](https://github.com/myparcelnl/pdk/commit/060867c122fe8ebbc582dcbc93ec81f0afe8bb3a))

## [2.14.1](https://github.com/myparcelnl/pdk/compare/v2.14.0...v2.14.1) (2023-08-10)


### :bug: Bug Fixes

* **cart-calculation:** fix mailbox packages over 2kg being allowed ([#147](https://github.com/myparcelnl/pdk/issues/147)) ([a126a54](https://github.com/myparcelnl/pdk/commit/a126a549aac5429bac030828842871f4cbdce811))

## [2.14.0](https://github.com/myparcelnl/pdk/compare/v2.13.0...v2.14.0) (2023-08-09)


### :sparkles: New Features

* **product:** support product variants ([#143](https://github.com/myparcelnl/pdk/issues/143)) ([937800e](https://github.com/myparcelnl/pdk/commit/937800ea49391664324ce1cb4a99d6902125a578))


### :bug: Bug Fixes

* **cast:** prevent circular reference ([#148](https://github.com/myparcelnl/pdk/issues/148)) ([c8c9fde](https://github.com/myparcelnl/pdk/commit/c8c9fde1d19266f2849dfc21852ba6e5c7c450cc))
* **model:** make 'boolean' cast work the same as 'bool' ([4c22bf4](https://github.com/myparcelnl/pdk/commit/4c22bf4520282e80d5cf4d03ba2de748421ddee7))
* **shipments:** fix carrier defaulting to postnl ([#151](https://github.com/myparcelnl/pdk/issues/151)) ([1639cdf](https://github.com/myparcelnl/pdk/commit/1639cdf63ea25611c6e49eb85aece23490cfa1ca))

## [2.13.0](https://github.com/myparcelnl/pdk/compare/v2.12.3...v2.13.0) (2023-08-08)


### :zap: Performance Improvements

* reduce amount of stored data and calls to toArray ([#135](https://github.com/myparcelnl/pdk/issues/135)) ([25bca7c](https://github.com/myparcelnl/pdk/commit/25bca7c306a599cbb0d0bae27f6608a5e54c0a24))


### :bug: Bug Fixes

* **delivery-options:** do not return date if it's in the past ([#138](https://github.com/myparcelnl/pdk/issues/138)) ([759b204](https://github.com/myparcelnl/pdk/commit/759b204899b10d9f6ccfcc1b2bb7a11213a80a54))
* **deps:** move unnecessary dependencies to require-dev ([178863d](https://github.com/myparcelnl/pdk/commit/178863d615024d08b02ea87194b6ea3e2e1f4d89))
* **orders:** fix validation of evening delivery ([2accccc](https://github.com/myparcelnl/pdk/commit/2accccc4fe706c2bb5a41be6c099d5908ad25321))
* **orders:** use endpoints correctly ([#142](https://github.com/myparcelnl/pdk/issues/142)) ([c558c01](https://github.com/myparcelnl/pdk/commit/c558c0155c104ecdd902563f15c0f72daf4d7dba))
* **settings:** fix saved invalid api key causing errors ([#149](https://github.com/myparcelnl/pdk/issues/149)) ([7ba4a02](https://github.com/myparcelnl/pdk/commit/7ba4a02e29ab164534cb2ce0fb86809d1eec3e6f))
* **settings:** hide "barcode in note title" when barcode in note is off ([#136](https://github.com/myparcelnl/pdk/issues/136)) ([8ba980e](https://github.com/myparcelnl/pdk/commit/8ba980e19320823a2a0cd6dc2da2af834a753e31))
* support more date formats ([#139](https://github.com/myparcelnl/pdk/issues/139)) ([2f1ee2f](https://github.com/myparcelnl/pdk/commit/2f1ee2f3fe10bf881860eb45cdb890c44970ecb6))


### :sparkles: New Features

* **carriers:** enable bpost and dpd for myparcel be ([#150](https://github.com/myparcelnl/pdk/issues/150)) ([5cb9203](https://github.com/myparcelnl/pdk/commit/5cb9203eca8df35363d71586e44455d30fbe402a))
* **cron:** allow more flexible use of the cron service ([#145](https://github.com/myparcelnl/pdk/issues/145)) ([3a64d97](https://github.com/myparcelnl/pdk/commit/3a64d97699d1923b730fe38ae038d2e196af348f))
* **dhl:** support hide_sender option ([#140](https://github.com/myparcelnl/pdk/issues/140)) ([e74fee6](https://github.com/myparcelnl/pdk/commit/e74fee68651fb30810aed253d34c9ea34b9c7b1e))
* **frontend:** allow rendering components at any moment after initial render ([#144](https://github.com/myparcelnl/pdk/issues/144)) ([02302a8](https://github.com/myparcelnl/pdk/commit/02302a8673ead97390b7cdb0592dbd176c17f72c))
* **order:** add shorthand method to check if order is deliverable ([#137](https://github.com/myparcelnl/pdk/issues/137)) ([9a3506d](https://github.com/myparcelnl/pdk/commit/9a3506d221e44aaa02af89d8ad67c59b432fdbf0))

## [2.12.3](https://github.com/myparcelnl/pdk/compare/v2.12.2...v2.12.3) (2023-07-26)


### :bug: Bug Fixes

* **notifications:** improve logic and increase coverage ([fe11473](https://github.com/myparcelnl/pdk/commit/fe114733e9c957dfa6608ac95dc19f9f571eb0cf))

## [2.12.2](https://github.com/myparcelnl/pdk/compare/v2.12.1...v2.12.2) (2023-07-26)


### :bug: Bug Fixes

* **settings:** fix carrier and product settings not being respected when exporting ([#134](https://github.com/myparcelnl/pdk/issues/134)) ([749819f](https://github.com/myparcelnl/pdk/commit/749819f3babe60bc890ad391cfe64eab0a22544e))

## [2.12.1](https://github.com/myparcelnl/pdk/compare/v2.12.0...v2.12.1) (2023-07-21)


### :bug: Bug Fixes

* **actions:** correct responses of delete and update account ([#129](https://github.com/myparcelnl/pdk/issues/129)) ([0fdd205](https://github.com/myparcelnl/pdk/commit/0fdd2052c12dbf21ae4d11f758299ef4bfdb1670))

## [2.12.0](https://github.com/myparcelnl/pdk/compare/v2.11.1...v2.12.0) (2023-07-21)


### :sparkles: New Features

* **fulfilment:** save order note uuids from api ([#130](https://github.com/myparcelnl/pdk/issues/130)) ([b068632](https://github.com/myparcelnl/pdk/commit/b06863207ad3157049244b0442063cba396185cb))
* **settings:** fill carrier settings on fresh installation ([#127](https://github.com/myparcelnl/pdk/issues/127)) ([587cbcc](https://github.com/myparcelnl/pdk/commit/587cbcc18ec1a65dec7af3abc0685c47cc402157))


### :bug: Bug Fixes

* **api:** fix error when composer can't be used normally ([#128](https://github.com/myparcelnl/pdk/issues/128)) ([217d11c](https://github.com/myparcelnl/pdk/commit/217d11cc6a29de65b28dd32e62c6b267d863ef28))
* **export:** prevent invalid delivery date ([#126](https://github.com/myparcelnl/pdk/issues/126)) ([d34624d](https://github.com/myparcelnl/pdk/commit/d34624de2167c33dbb64c46c46f1b9aad2ef2b04))
* **order:** stop storing carrier details in db ([#125](https://github.com/myparcelnl/pdk/issues/125)) ([35dd3bb](https://github.com/myparcelnl/pdk/commit/35dd3bbfa17d2ffcb65b2280ff0fb860c58dce35))

## [2.11.1](https://github.com/myparcelnl/pdk/compare/v2.11.0...v2.11.1) (2023-07-18)


### :bug: Bug Fixes

* **tests:** include tests/Api and tests/Datasets in published package ([4cc0335](https://github.com/myparcelnl/pdk/commit/4cc033510cf83468257fb8b4161f525186d0167b))


### :zap: Performance Improvements

* **order:** improve export order performance ([ec9db3f](https://github.com/myparcelnl/pdk/commit/ec9db3f3c8f1718854e1793d7ba887076f8c9a6d))

## [2.11.0](https://github.com/myparcelnl/pdk/compare/v2.10.0...v2.11.0) (2023-07-18)


### :sparkles: New Features

* **fulfilment:** add order notes ([#122](https://github.com/myparcelnl/pdk/issues/122)) ([88a4c83](https://github.com/myparcelnl/pdk/commit/88a4c83ea70b13790f261ac63022623f3dc395d7))

## [2.10.0](https://github.com/myparcelnl/pdk/compare/v2.9.2...v2.10.0) (2023-07-18)


### :sparkles: New Features

* **actions:** add delete account action ([#124](https://github.com/myparcelnl/pdk/issues/124)) ([0373ca9](https://github.com/myparcelnl/pdk/commit/0373ca9c486a344a3d08943e33c952ae29a1a116))
* **orders:** honor the "share customer information" setting ([#113](https://github.com/myparcelnl/pdk/issues/113)) ([6238419](https://github.com/myparcelnl/pdk/commit/6238419a3376027fb630ef997f25b698de2c72b0))

## [2.9.2](https://github.com/myparcelnl/pdk/compare/v2.9.1...v2.9.2) (2023-07-17)


### :bug: Bug Fixes

* **container:** fix cache class name ([6e11176](https://github.com/myparcelnl/pdk/commit/6e1117698276df9bfe4c69abd36a193aec0e9caa))

## [2.9.1](https://github.com/myparcelnl/pdk/compare/v2.9.0...v2.9.1) (2023-07-17)


### :bug: Bug Fixes

* **container:** clear container cache on install/upgrade/uninstall ([#123](https://github.com/myparcelnl/pdk/issues/123)) ([06549f7](https://github.com/myparcelnl/pdk/commit/06549f7d656c6b342f889d2c96e4a1742b013cb6))

## [2.9.0](https://github.com/myparcelnl/pdk/compare/v2.8.0...v2.9.0) (2023-07-13)


### :bug: Bug Fixes

* **fulfilment:** fix exporting orders ([dadcf6a](https://github.com/myparcelnl/pdk/commit/dadcf6adad06e3c74225cd01613e94309b5e9516))


### :zap: Performance Improvements

* **container:** use container file cache in production ([5e95705](https://github.com/myparcelnl/pdk/commit/5e95705563d917f76a88d0af87b3250ed8e4a82d))


### :sparkles: New Features

* **container:** add isPhpVersionSupported property ([aeb2e2e](https://github.com/myparcelnl/pdk/commit/aeb2e2e3f0c8ba38ad1a7630baad0d710b927dd9))
* **fulfilment:** add get order request ([02989e7](https://github.com/myparcelnl/pdk/commit/02989e7a9de08eaa4a624e0e9ed17337bf917a94))
* **webhook:** add logic to order status change webhook ([5805f56](https://github.com/myparcelnl/pdk/commit/5805f56617ad990fc233699a08547a470c6e1e6e))
* **webhook:** add logic to shipment label created and status change webhooks ([12c1ddd](https://github.com/myparcelnl/pdk/commit/12c1ddd1d3f57a5aed0eb9e8cf810deb36ebfb32))
* **webhook:** improve validation ([d830fc6](https://github.com/myparcelnl/pdk/commit/d830fc626ab3a5f6b6c5230dc6f7c591ddc77a0c))

## [2.8.0](https://github.com/myparcelnl/pdk/compare/v2.7.0...v2.8.0) (2023-07-10)


### :sparkles: New Features

* **attributes:** convert string true and false to int/bool ([#114](https://github.com/myparcelnl/pdk/issues/114)) ([4ec94ab](https://github.com/myparcelnl/pdk/commit/4ec94abe7cb231c29b9d5dd8bb3702b59cee02da))
* **webhooks:** add logic to shop update webhooks ([#115](https://github.com/myparcelnl/pdk/issues/115)) ([731ddc2](https://github.com/myparcelnl/pdk/commit/731ddc2aa286d696c9c2ff01d5aa992c240b4ad5))


### :bug: Bug Fixes

* **actions:** allow update account without passing new settings ([#118](https://github.com/myparcelnl/pdk/issues/118)) ([0935f35](https://github.com/myparcelnl/pdk/commit/0935f35fc18173f655369a5e083bacf3e3379d79))
* **fulfilment:** fix validation errors on export ([#117](https://github.com/myparcelnl/pdk/issues/117)) ([2c82b36](https://github.com/myparcelnl/pdk/commit/2c82b36ee353f2721334119aaaefc0ddce854b1e))
* **settings:** fix error when opening plugin settings without account ([#119](https://github.com/myparcelnl/pdk/issues/119)) ([996c3f5](https://github.com/myparcelnl/pdk/commit/996c3f50656300ecdc4ba00b8d994c68ba74be9b))

## [2.7.0](https://github.com/myparcelnl/pdk/compare/v2.6.5...v2.7.0) (2023-07-05)


### :sparkles: New Features

* **settings:** update general settings ([3f84f3c](https://github.com/myparcelnl/pdk/commit/3f84f3c462e92a9087f29fb78f400777611634a9))

## [2.6.5](https://github.com/myparcelnl/pdk/compare/v2.6.4...v2.6.5) (2023-07-03)


### :bug: Bug Fixes

* **deps:** always use latest v5 of myparcelnl/delivery-options ([5386fba](https://github.com/myparcelnl/pdk/commit/5386fba683f759b6a6f1fd022daace0525c10c83))

## [2.6.4](https://github.com/myparcelnl/pdk/compare/v2.6.3...v2.6.4) (2023-07-03)


### :bug: Bug Fixes

* **carrier:** fix undefined index error ([75bbdbf](https://github.com/myparcelnl/pdk/commit/75bbdbff40601bccc72691f64216ef75b4383425))

## [2.6.3](https://github.com/myparcelnl/pdk/compare/v2.6.2...v2.6.3) (2023-07-03)


### :bug: Bug Fixes

* **views:** rename tristate to tri-state ([f8ae8c4](https://github.com/myparcelnl/pdk/commit/f8ae8c46807014f8cbb0bcb0c03f739c42d3a61c))

## [2.6.2](https://github.com/myparcelnl/pdk/compare/v2.6.1...v2.6.2) (2023-06-30)


### :bug: Bug Fixes

* **product-settings:** fix product settings view not showing ([#109](https://github.com/myparcelnl/pdk/issues/109)) ([c420fd0](https://github.com/myparcelnl/pdk/commit/c420fd0ad28432a19eda673034afc5780f062635))

## [2.6.1](https://github.com/myparcelnl/pdk/compare/v2.6.0...v2.6.1) (2023-06-29)


### :bug: Bug Fixes

* **installer:** move logic that updates installed version into an overridable method ([8dc3342](https://github.com/myparcelnl/pdk/commit/8dc3342f6e4bbdeb5614fe3dd5e05775896be76f))

## [2.6.0](https://github.com/myparcelnl/pdk/compare/v2.5.2...v2.6.0) (2023-06-29)


### :sparkles: New Features

* **installer:** allow passing arbitrary arguments to install and uninstall methods ([#112](https://github.com/myparcelnl/pdk/issues/112)) ([b91fbb3](https://github.com/myparcelnl/pdk/commit/b91fbb3535886c7741ec6674a1c0c23266331142))

## [2.5.2](https://github.com/myparcelnl/pdk/compare/v2.5.1...v2.5.2) (2023-06-28)


### :bug: Bug Fixes

* improve carrier logic ([#110](https://github.com/myparcelnl/pdk/issues/110)) ([661e944](https://github.com/myparcelnl/pdk/commit/661e9441dc4355b873efbd378d67ff0979d3b714))

## [2.5.1](https://github.com/myparcelnl/pdk/compare/v2.5.0...v2.5.1) (2023-06-21)


### :bug: Bug Fixes

* **settings:** fix incorrect values in package type selects ([#107](https://github.com/myparcelnl/pdk/issues/107)) ([120c576](https://github.com/myparcelnl/pdk/commit/120c576e1cfe34adfe933143bfbb95ad9d5e741d))
* **shipments:** fix carrier reverting to default on exporting ([#108](https://github.com/myparcelnl/pdk/issues/108)) ([24352ee](https://github.com/myparcelnl/pdk/commit/24352ee3b47041df71ba2503b369d70ad40099bf))

## [2.5.0](https://github.com/myparcelnl/pdk/compare/v2.4.2...v2.5.0) (2023-06-19)


### :bug: Bug Fixes

* **settings:** fix product settings being unable to render  ([#104](https://github.com/myparcelnl/pdk/issues/104)) ([94b475b](https://github.com/myparcelnl/pdk/commit/94b475b6bc3fd6075dbc161e8efe419332bf8db8))


### :sparkles: New Features

* **actions:** add update product settings action ([#106](https://github.com/myparcelnl/pdk/issues/106)) ([1302c1b](https://github.com/myparcelnl/pdk/commit/1302c1b5cb982121f575c22a4d3cf2ab5f903ac5))

## [2.4.2](https://github.com/myparcelnl/pdk/compare/v2.4.1...v2.4.2) (2023-06-08)


### :bug: Bug Fixes

* **views:** correct di reference to country service ([59bef78](https://github.com/myparcelnl/pdk/commit/59bef786b56cdc606f1afbfe8e2e4ee4a8ead691))

## [2.4.1](https://github.com/myparcelnl/pdk/compare/v2.4.0...v2.4.1) (2023-06-08)


### :bug: Bug Fixes

* **actions:** improve update account action ([#105](https://github.com/myparcelnl/pdk/issues/105)) ([c5c00e2](https://github.com/myparcelnl/pdk/commit/c5c00e21efefad17089b13c2bb16a370a0bb9151))

## [2.4.0](https://github.com/myparcelnl/pdk/compare/v2.3.0...v2.4.0) (2023-06-07)


### :sparkles: New Features

* **country:** add isLocalCountry method ([ba519bf](https://github.com/myparcelnl/pdk/commit/ba519bf3dc549e5d109b2e75528c4c9c3b0c36c7))


### :bug: Bug Fixes

* **settings:** improve settings views ([9857b7f](https://github.com/myparcelnl/pdk/commit/9857b7fc9b60c36aa575c59c2899aeb3f83a4099))

## [2.3.0](https://github.com/myparcelnl/pdk/compare/v2.2.2...v2.3.0) (2023-06-06)


### :sparkles: New Features

* **settings:** add calculation by factor option to insurance ([#98](https://github.com/myparcelnl/pdk/issues/98)) ([b176554](https://github.com/myparcelnl/pdk/commit/b176554d9aa2cdf028d0eb4669328208093fa394))

## [2.2.2](https://github.com/myparcelnl/pdk/compare/v2.2.1...v2.2.2) (2023-06-01)


### :bug: Bug Fixes

* **tests:** include tests/Bootstrap in published package ([258f8c5](https://github.com/myparcelnl/pdk/commit/258f8c5c6d58e72ddb5b7a53ab8ae99b14c56305))

## [2.2.1](https://github.com/myparcelnl/pdk/compare/v2.2.0...v2.2.1) (2023-06-01)


### :bug: Bug Fixes

* **tests:** include pest helper files in published package ([18086c1](https://github.com/myparcelnl/pdk/commit/18086c12b7b67d2eea2c03ceec42e26dc5090015))

## [2.2.0](https://github.com/myparcelnl/pdk/compare/v2.1.0...v2.2.0) (2023-06-01)


### :sparkles: New Features

* **shipments:** allow same day delivery for all dhl shipments ([#100](https://github.com/myparcelnl/pdk/issues/100)) ([bcbb463](https://github.com/myparcelnl/pdk/commit/bcbb4636cba0092150e04f3cf3ce5b1fadb577d3))


### :bug: Bug Fixes

* **shipments:** fix error when exporting order to shipments ([#102](https://github.com/myparcelnl/pdk/issues/102)) ([279be05](https://github.com/myparcelnl/pdk/commit/279be05f2b5ff7e1d44f3092a952d44277e5f1bb))

## [2.1.0](https://github.com/myparcelnl/pdk/compare/v2.0.0...v2.1.0) (2023-05-31)


### :sparkles: New Features

* allow the pdk class to be overridden ([#96](https://github.com/myparcelnl/pdk/issues/96)) ([32a7263](https://github.com/myparcelnl/pdk/commit/32a7263dc22d06213c05cb3df7092db581051a02))


### :bug: Bug Fixes

* **checkout:** fix delivery options header not showing up ([#99](https://github.com/myparcelnl/pdk/issues/99)) ([7af0537](https://github.com/myparcelnl/pdk/commit/7af053781d220206817dfa8abcdf3dd3d752581e))
* correct address fields ([#101](https://github.com/myparcelnl/pdk/issues/101)) ([3fed974](https://github.com/myparcelnl/pdk/commit/3fed974f6ed2a1690fc23d2e61c4723d8877a83f))

## [2.0.0](https://github.com/myparcelnl/pdk/compare/v1.37.0...v2.0.0) (2023-05-15)


### ⚠ BREAKING CHANGES

* move classes to clearer namespaces
* **facade:** add final modifier to all facades
* **facade:** rename Facade RenderService to Frontend
* **facade:** rename RenderServiceInterface to FrontendRenderServiceInterface
* **facade:** rename Facade DefaultLogger to Logger
* **facade:** rename Facade LanguageService to Language
* moves interfaces to different namespaces

### :zap: Performance Improvements

* **shipments:** do not fetch orders on deleting shipments ([ca01f9d](https://github.com/myparcelnl/pdk/commit/ca01f9daa0526ffb6f11b6d445753d7fe819fcca))
* **shipments:** do not return deleted shipments in order data context ([96e5ffc](https://github.com/myparcelnl/pdk/commit/96e5ffca9a1164656841a9505fe45f015cd6c3dd))


### :sparkles: New Features

* **actions:** pass request query parameters to ContextService::createContexts() ([4e8fcf5](https://github.com/myparcelnl/pdk/commit/4e8fcf5d97ebc037ef0ea957a6dd912dd7084329))
* add bootstrapper class and force appInfo to be set ([2688e34](https://github.com/myparcelnl/pdk/commit/2688e34738830e6a3736edef8be1d6700bee02ec))
* add default minimum php version to config ([26934e0](https://github.com/myparcelnl/pdk/commit/26934e0712f998dfe36ce6aa4f860e206077f7b1))
* add extra properties to fulfilment shipment ([f366828](https://github.com/myparcelnl/pdk/commit/f366828a431e04a6117afcbb3c60776b64655092))
* add installer facade ([4ca8349](https://github.com/myparcelnl/pdk/commit/4ca83490f9473a659e2018ddfcb55c09d813a629))
* add responseProperty to requests ([2cb2e5e](https://github.com/myparcelnl/pdk/commit/2cb2e5efeca9966fd5c75bb54d8ab6d0e1914b8b))
* **admin:** add platform data to global context ([8441fdb](https://github.com/myparcelnl/pdk/commit/8441fdb36b7d8c2ce4d0d8ba622f87ba7ab356a4))
* can send notifications from backend to frontend ([f253774](https://github.com/myparcelnl/pdk/commit/f253774d0939e88fcfd1d682eb85491912fab37d))
* **capabilities:** add dhlparcelconnect ([83802b3](https://github.com/myparcelnl/pdk/commit/83802b353cd85187500c5e0b3ff6f5d503af4a6c))
* **currency:** add format method ([660f892](https://github.com/myparcelnl/pdk/commit/660f892dd614de9c265b123bb411d1f402dbd20d))
* improve settings views ([3e33e53](https://github.com/myparcelnl/pdk/commit/3e33e53b84fc965cb6bd62b4f88ddc16a74f2784))
* make container values more granular ([f2bc19d](https://github.com/myparcelnl/pdk/commit/f2bc19dc46741b42d44a55a54d390165f70e4317))
* pdk frontend ([19c5276](https://github.com/myparcelnl/pdk/commit/19c5276bf7c0017e37daaddf77ee8fb0703779f2))
* **product:** add fit in digital stamp ([d6f971c](https://github.com/myparcelnl/pdk/commit/d6f971ca838a3cd36c12bd45c449dae0ea53efaf))
* **response:** allow passing headers ([15f19e4](https://github.com/myparcelnl/pdk/commit/15f19e4c59f3776ff68a7b8e97fa00097f038a79))
* send notification when order validation fails during export ([6d9cd5f](https://github.com/myparcelnl/pdk/commit/6d9cd5f74c3c85f7da61efd046c1b00bf1cf18c6))
* send notifications from backend wip ([f70e122](https://github.com/myparcelnl/pdk/commit/f70e122ec948d25710b7755beafcfa8ac621f2d1))
* send notifications from backend wip ([30f6948](https://github.com/myparcelnl/pdk/commit/30f6948a2e998c730afa5bd410eb054d745bf562))
* **settings:** add divider to label settings view ([ba56f53](https://github.com/myparcelnl/pdk/commit/ba56f533834300f21819607db0a73e46c2493c20))
* **settings:** add input for enabled package types ([c8b46f8](https://github.com/myparcelnl/pdk/commit/c8b46f85726fd3715e084d69bd3875db0360ba77))
* **validation:** add dhleuroplus ([fd37d0f](https://github.com/myparcelnl/pdk/commit/fd37d0f07ecd803c618d3a5f852d479e2ccfb61a))


* **facade:** add final modifier to all facades ([051d78e](https://github.com/myparcelnl/pdk/commit/051d78ed67164327643546a3ad4ec0abe375b2ca))
* **facade:** rename DefaultLogger to Logger ([5fab918](https://github.com/myparcelnl/pdk/commit/5fab9187879e63e37b4bd65db06ad5c1ced2381c))
* **facade:** rename LanguageService to Language ([6d797a4](https://github.com/myparcelnl/pdk/commit/6d797a4a71bb801fe9c24fdfc8f404f7e6edf6ab))
* **facade:** rename RenderService to Frontend ([f4577d4](https://github.com/myparcelnl/pdk/commit/f4577d45171fd47d6983a76498a3efaf1a7e47ed))
* move classes to clearer namespaces ([43901d7](https://github.com/myparcelnl/pdk/commit/43901d7dc98c33ddf0510bba1309bb62ba7817ac))
* normalize interfaces ([7ec8b22](https://github.com/myparcelnl/pdk/commit/7ec8b22190eba524ad8d54a154020bbb156b72d2))


### :bug: Bug Fixes

* **actions:** allow passing cart in FetchCheckoutContext ([82c059f](https://github.com/myparcelnl/pdk/commit/82c059f5dd6da14b9fd2d585d6a3ccdd38779ed8))
* **actions:** allow passing cart in FetchCheckoutContext ([b431e2e](https://github.com/myparcelnl/pdk/commit/b431e2e53561a194190b215019e7f77a47af1d1b))
* **actions:** rename fetch shipments to update shipments ([b517b7a](https://github.com/myparcelnl/pdk/commit/b517b7a69f877f6d11735a76eb5d9b99aff97d6b))
* add allowed carrier ids ([52df8b0](https://github.com/myparcelnl/pdk/commit/52df8b0c21c50c6193fcb19da236854696d265a6))
* add codeEditor ([83c560e](https://github.com/myparcelnl/pdk/commit/83c560ec1c9ac30b3b8170ac139c4c18c3149b50))
* add custom css to checkoutsettings ([8da2629](https://github.com/myparcelnl/pdk/commit/8da26294e7b45f481e22eb0d3265e5abc65b6e24))
* add deliveryDaysWindow to carrier settings attributes ([5857126](https://github.com/myparcelnl/pdk/commit/5857126eff9fedb92114e291fec2ddf6664eb56a))
* add logger to shipment validation ([d3207a4](https://github.com/myparcelnl/pdk/commit/d3207a47360d32700181d26dff08ef29ce120a53))
* allow empty values in shipment options ([d9a43ae](https://github.com/myparcelnl/pdk/commit/d9a43ae8bdfd30d1d1c644516a102a9caff0642b))
* allow export of fulfilment order ([4f8c2ac](https://github.com/myparcelnl/pdk/commit/4f8c2ac787657a8bf9714a5cf21c730f2a751639))
* allow fetchcontext action to be used on frontend and backend ([e1bc671](https://github.com/myparcelnl/pdk/commit/e1bc67151704b602dc388708343bd5b7d3390964))
* allow guzzle 5 client ([fd81828](https://github.com/myparcelnl/pdk/commit/fd818280786cf99b106152686b70cd437fdbc089))
* **api:** remove error option ([9a1dde5](https://github.com/myparcelnl/pdk/commit/9a1dde5912a44bbdf187d0b82f0b0a454766f7c0))
* **carrier:** fix array_merge null notice ([fc1fe3a](https://github.com/myparcelnl/pdk/commit/fc1fe3a4ef2cbfea42fb0a6167b66ed2c0020f93))
* **carrier:** fix finding carrier again ([3674401](https://github.com/myparcelnl/pdk/commit/36744018d00901eafe2287ea46830b4bc4ce868b))
* **carrier:** fix finding name matching id ([b14b603](https://github.com/myparcelnl/pdk/commit/b14b6032f2f109636102265fbc887c1c238aaeba))
* **carrier:** use default carrier if none was passed ([43555ac](https://github.com/myparcelnl/pdk/commit/43555ac9172d0983a16cd274a3a89b89f896c850))
* **carrier:** use name OR id if already present ([04a93d3](https://github.com/myparcelnl/pdk/commit/04a93d30b87068613377dd3c156490dec356ae89))
* change api back to live api ([1ad82be](https://github.com/myparcelnl/pdk/commit/1ad82be221aac42841a70c36fb59eb4338fc2426))
* **checkout:** get correct data in checkout context ([cfca342](https://github.com/myparcelnl/pdk/commit/cfca3426607823bfc330c4e639d02bfdfd5b1278))
* **checkout:** pass tax fields data ([3631bf6](https://github.com/myparcelnl/pdk/commit/3631bf6a4a7a4113e0d45922bcf0638e05201739))
* **checkout:** return allowed package types correctly ([534c3e2](https://github.com/myparcelnl/pdk/commit/534c3e2fed4408b8473935843a0f5945d2357c2b))
* **checkout:** update delivery options config ([323694c](https://github.com/myparcelnl/pdk/commit/323694c77dfac8c3932b6c2d851bc513ef227b22))
* construct carrier completely ([c5ca852](https://github.com/myparcelnl/pdk/commit/c5ca8527389a3281efdd282ac3881f9369818b2e))
* correct confusing carrier settings names ([be3dde4](https://github.com/myparcelnl/pdk/commit/be3dde472a854d77420ed5351b44b2314717adc1))
* delivery options config test ([1e17b96](https://github.com/myparcelnl/pdk/commit/1e17b9667027b14da3b3fe416e4a4efda049918b))
* **delivery-options:** convert package and delivery types correctly ([ded621a](https://github.com/myparcelnl/pdk/commit/ded621a2e0ceec6044c33d2d1c06733a1bf43514))
* **delivery-options:** fix error when data is null ([3812478](https://github.com/myparcelnl/pdk/commit/3812478211645a95fb9a008bb6c0d01f8d7e6ac5))
* **delivery-options:** fix same day delivery option ([219befc](https://github.com/myparcelnl/pdk/commit/219befcdb96e579de137e3c8a6f278bb7fefd337))
* **endpoints:** fix merging of backend/frontend and shared endpoints ([95ff264](https://github.com/myparcelnl/pdk/commit/95ff2649379ae87fcd2988fc5c89b8c4a6b005e3))
* export same-day delivery for dhl for you ([d86c7f6](https://github.com/myparcelnl/pdk/commit/d86c7f6093a6b006a8870fe7ddb0afe954c84469))
* **facade:** fix incorrect reference to installer service interface ([46227e6](https://github.com/myparcelnl/pdk/commit/46227e6afd23a52cbfe295cdad5af68199a920d5))
* fix error in carrier ([65a5aeb](https://github.com/myparcelnl/pdk/commit/65a5aeb0c956490136b370043abe29eea7e0c014))
* fix insurance always ending up to be 5000 ([7cc92a6](https://github.com/myparcelnl/pdk/commit/7cc92a6130c61e9dd320dc13cf69686c3385e103))
* fix labels on large bulk print ([39cb78e](https://github.com/myparcelnl/pdk/commit/39cb78e4992b809c608f9e01f337f6b81aea11a7))
* fix labels on large bulk print ([99d5a10](https://github.com/myparcelnl/pdk/commit/99d5a106d976dbcb55b9f55dd8f8a3bd1e33d300))
* fix print options modal ([92d8991](https://github.com/myparcelnl/pdk/commit/92d8991d5ecd644eb46d711d76664b8dcfd11a33))
* fix request body for return shipments ([4b02231](https://github.com/myparcelnl/pdk/commit/4b022314a79e070a39e87a3d7a2da561ea3eac2a))
* fix tests (wip) ([bf79da6](https://github.com/myparcelnl/pdk/commit/bf79da6361800572ad909b778e7ba5d155d070ce))
* get carrier id for configuration ([7169c36](https://github.com/myparcelnl/pdk/commit/7169c36dfffe5c723f5467de99d4d0f3d1219707))
* get default time zone from config ([e4ede42](https://github.com/myparcelnl/pdk/commit/e4ede42cc86d645a8d2517b1f195d268546370bf))
* handle fetching account better ([6cda097](https://github.com/myparcelnl/pdk/commit/6cda0971759b97dbe330f85d0edf78bfe54b82e1))
* implement shared actions correctly ([07396d4](https://github.com/myparcelnl/pdk/commit/07396d498a7b6636b709f587d46d7ed32f5d950d))
* improve carrier logic ([6078838](https://github.com/myparcelnl/pdk/commit/6078838c5f297f4f29603d966cdf5b7db0781b18))
* **language:** allow null when translating arrays ([95d974d](https://github.com/myparcelnl/pdk/commit/95d974dcbd9d63753610535a4a5a9b3d782e4dc5))
* leave out caching of capabilities ([400e33e](https://github.com/myparcelnl/pdk/commit/400e33e4d010a37394de951fb1ff3aa571878d16))
* **model:** fix changing case of attributes ([9a1f56f](https://github.com/myparcelnl/pdk/commit/9a1f56fe2d45fc0ac2583ba7988861599e114196))
* **model:** improve casting logic ([699cec0](https://github.com/myparcelnl/pdk/commit/699cec01ee645fbaf4f90d610a12832faed3942f))
* **orders:** fix customs declaration error when exporting orders ([435a444](https://github.com/myparcelnl/pdk/commit/435a444bb93f66258f7978ffd7cd1f1ab824bc64))
* **orders:** fix printing orders and shipments ([6b1647b](https://github.com/myparcelnl/pdk/commit/6b1647be1c1a233ed6986bf469b72742238c0d56))
* **orders:** hard delete shipments ([7088cef](https://github.com/myparcelnl/pdk/commit/7088cef7b8a96beb40a612fcd313286be3b58377))
* **orders:** ignore deleted shipments ([3af9f69](https://github.com/myparcelnl/pdk/commit/3af9f6986ac6a29f4652624de2db43754b8c129a))
* prevent undefined key error ([2e0f915](https://github.com/myparcelnl/pdk/commit/2e0f9151a7cf9979f8887a65b9a73e58c2d72445))
* **print:** fix error on positions parameter ([b58fab7](https://github.com/myparcelnl/pdk/commit/b58fab78fb654078d073f28d509b17f1b7a075d2))
* **productsettings:** remove separate fit in digital stamp use package type ([f6d733d](https://github.com/myparcelnl/pdk/commit/f6d733df61a0d103c816fc0f5b0062e0071b18f4))
* remove allowed carrier ids ([aa8d842](https://github.com/myparcelnl/pdk/commit/aa8d842b64742d3719f4866bc18728c7a45f967d))
* remove extra data attribute from update plugin settings response ([57c71e5](https://github.com/myparcelnl/pdk/commit/57c71e5cd7923cf73b0c8b4c672457a10e5f2b31))
* remove pdf option from print shipments ([5a86c7d](https://github.com/myparcelnl/pdk/commit/5a86c7dc30fe8e1d758632c7164a2282e34659ea))
* **repository:** always retrieve full setting key ([7d9a492](https://github.com/myparcelnl/pdk/commit/7d9a492f523f2c4141206e3bfd240246628ac19c))
* return correct insurance value ([ffbd419](https://github.com/myparcelnl/pdk/commit/ffbd419da65e09df95580715b389ba18916077fa))
* return correct insurance value ([1d2d955](https://github.com/myparcelnl/pdk/commit/1d2d955f2e4212623e6997dbe7c8c3f38230c711))
* set samedaydelivery true for non-pilot customers ([410fc03](https://github.com/myparcelnl/pdk/commit/410fc035720577626d3a57288f9ab22846a7fd49))
* **settings:** change a4/a6 values to lowercase ([d2b760d](https://github.com/myparcelnl/pdk/commit/d2b760da0f46b38efe6c37b5f7b18a36877bf800))
* **settings:** correct shared print settings labels ([377db04](https://github.com/myparcelnl/pdk/commit/377db04be4ecb41940174628f45b6832c199f4f3))
* **settings:** fix insurance formatting and translations ([2885501](https://github.com/myparcelnl/pdk/commit/28855018c46bf0cbe25d056925bffa3eac39028b))
* **settings:** fix missing select options in default package type ([0d35372](https://github.com/myparcelnl/pdk/commit/0d353728bfad44b919eef6357495068b2a4cf102))
* **settings:** fix position input ([0957f40](https://github.com/myparcelnl/pdk/commit/0957f4004b757f14946d0e820ed3f725dc497cf6))
* **settings:** fix type error ([24c9c57](https://github.com/myparcelnl/pdk/commit/24c9c5790971dd4450958a50dbb288c3e2f8d926))
* **settings:** get delivery options positions correctly ([9b8f05b](https://github.com/myparcelnl/pdk/commit/9b8f05b71b398f16a6cc75e49eed09afbf0d0d47))
* **settings:** improve form elements ([d4c0e5a](https://github.com/myparcelnl/pdk/commit/d4c0e5ab723be271a0192869ba64f96f63dd1d72))
* **settings:** only show label position when format is a4 ([3625b7d](https://github.com/myparcelnl/pdk/commit/3625b7d75a04d214cd35ba8b1e31ca53713f3dd1))
* **settings:** remove extra conversion to cents ([6e62437](https://github.com/myparcelnl/pdk/commit/6e6243790c0bae3b351caa30daadad46a4935c13))
* **settings:** translate country select in customs settings ([988b383](https://github.com/myparcelnl/pdk/commit/988b383028d4f957541ac63cd7a1688543ee372b))
* **settings:** translate country select in product settings ([e215bc0](https://github.com/myparcelnl/pdk/commit/e215bc0c140aab5f8a62cac61c5f14562e5bd609))
* **settings:** translate order statuses in order settings ([08831d3](https://github.com/myparcelnl/pdk/commit/08831d36a6c93dcf8ea872e7254ddd36240f7fb9))
* **shipments:** fetch consumer portal link with shipments ([94e3c89](https://github.com/myparcelnl/pdk/commit/94e3c892caeb10336361aba8c3ecfecf73fc1635))
* **shipments:** fix label position ([8d3ec42](https://github.com/myparcelnl/pdk/commit/8d3ec42d1da4a5351b69392d8087122f2089dd4e))
* **shipments:** only change updated if it was null ([8a4170b](https://github.com/myparcelnl/pdk/commit/8a4170b1a860b6328396c0717dafb93d6593e978))
* **translations:** translate "none" option in selects ([1579849](https://github.com/myparcelnl/pdk/commit/1579849633c901415d64979da1c2957c497ea669))
* update country codes ([5f1704c](https://github.com/myparcelnl/pdk/commit/5f1704c28d42ba96c4a50ea38310d562603cf4eb))
* update delivery options ([647ac09](https://github.com/myparcelnl/pdk/commit/647ac0975a515b03dbc721e6acf0b352b124ef94))
* update requests that return context ([efac645](https://github.com/myparcelnl/pdk/commit/efac6457836821c1d7c194bb64bd031b11db2dd2))
* use correct timezone for updated shipments ([94f6f93](https://github.com/myparcelnl/pdk/commit/94f6f9378cd5b9d8780d5a0e433d993fb7014e2c))
* **utils:** fix cache keys in cache function not always being unique ([6834498](https://github.com/myparcelnl/pdk/commit/68344988d3022230c0679c44061b317fe108b7cd))
* validate export shipments ([57495b1](https://github.com/myparcelnl/pdk/commit/57495b1ae81a887a67a9880a24f343dbb6e816fa))
* **webhook:** allow log context for symfony below 5.2 ([55b7142](https://github.com/myparcelnl/pdk/commit/55b7142707a84a68a6c59475388076132dfa5fa3))

## [1.37.0](https://github.com/myparcelnl/pdk/compare/v1.36.0...v1.37.0) (2023-03-09)


### :sparkles: New Features

* add bootstrapper class and force appInfo to be set ([#88](https://github.com/myparcelnl/pdk/issues/88)) ([0d7d93b](https://github.com/myparcelnl/pdk/commit/0d7d93b55ff1c02d7982fc5767153248d54afe64))

## [1.36.0](https://github.com/myparcelnl/pdk/compare/v1.35.0...v1.36.0) (2023-02-28)


### :bug: Bug Fixes

* **model:** fix flags not being passed to nested models ([#79](https://github.com/myparcelnl/pdk/issues/79)) ([2de423b](https://github.com/myparcelnl/pdk/commit/2de423b001bdfdbb9b1eba8cbcecb036726d0224))
* **shipments:** correct merging of shipment/order ids ([#80](https://github.com/myparcelnl/pdk/issues/80)) ([ac0bd0b](https://github.com/myparcelnl/pdk/commit/ac0bd0bc6067514af6618bda4bf0578cc79a7ba3))


### :sparkles: New Features

* add view service ([#75](https://github.com/myparcelnl/pdk/issues/75)) ([c88a503](https://github.com/myparcelnl/pdk/commit/c88a503f2ed3454f085a90555211f1b603c888d0))
* **language:** add more methods ([#85](https://github.com/myparcelnl/pdk/issues/85)) ([b499b44](https://github.com/myparcelnl/pdk/commit/b499b44fa4ce6b74346f49ea5f2f5ace4e742767))

## [1.35.0](https://github.com/myparcelnl/pdk/compare/v1.34.0...v1.35.0) (2023-02-27)


### :sparkles: New Features

* **country-service:** add getAllTranslatable method ([138af6b](https://github.com/myparcelnl/pdk/commit/138af6b7e9651a0f669157f5eb9464ed1d0267eb))

## [1.34.0](https://github.com/myparcelnl/pdk/compare/v1.33.0...v1.34.0) (2023-01-27)


### :bug: Bug Fixes

* **model:** fix flags being required on except ([be80df5](https://github.com/myparcelnl/pdk/commit/be80df5079b5bfe67723c2aae7aefbf9351d4f24))


### :sparkles: New Features

* **base:** increase consistency of services ([#73](https://github.com/myparcelnl/pdk/issues/73)) ([8272f10](https://github.com/myparcelnl/pdk/commit/8272f10beaba7739da7cc2cf236767afd6c1b076))

## [1.33.0](https://github.com/myparcelnl/pdk/compare/v1.32.1...v1.33.0) (2023-01-12)


### :sparkles: New Features

* **actions:** add multicollo to export ([#66](https://github.com/myparcelnl/pdk/issues/66)) ([43b7e5e](https://github.com/myparcelnl/pdk/commit/43b7e5eab9bbbd73db74f1ec2eebb87b27f97c24))

## [1.32.1](https://github.com/myparcelnl/pdk/compare/v1.32.0...v1.32.1) (2023-01-12)


### :bug: Bug Fixes

* improve shipments and orders ([#65](https://github.com/myparcelnl/pdk/issues/65)) ([d387c54](https://github.com/myparcelnl/pdk/commit/d387c546058a42c2a8165d6454e1407260e91601))

## [1.32.0](https://github.com/myparcelnl/pdk/compare/v1.31.0...v1.32.0) (2023-01-12)


### :sparkles: New Features

* **orders:** calculate totals via order lines ([#59](https://github.com/myparcelnl/pdk/issues/59)) ([cc9a644](https://github.com/myparcelnl/pdk/commit/cc9a644ffa9b32232fb4fe18938b7262839623ba))

## [1.31.0](https://github.com/myparcelnl/pdk/compare/v1.30.0...v1.31.0) (2022-12-22)


### :sparkles: New Features

* **model:** support date arrays ([#69](https://github.com/myparcelnl/pdk/issues/69)) ([f93a67f](https://github.com/myparcelnl/pdk/commit/f93a67f57820a857c30b14454c0b6a5a08c19729))

## [1.30.0](https://github.com/myparcelnl/pdk/compare/v1.29.1...v1.30.0) (2022-12-20)


### :sparkles: New Features

* add webhook repository ([#57](https://github.com/myparcelnl/pdk/issues/57)) ([8c97d57](https://github.com/myparcelnl/pdk/commit/8c97d57381aeee3dd2048085f9c1f760435746ba))

## [1.29.1](https://github.com/myparcelnl/pdk/compare/v1.29.0...v1.29.1) (2022-12-20)


### :bug: Bug Fixes

* **api:** correct user agent ([e367c74](https://github.com/myparcelnl/pdk/commit/e367c7498b61ffc97ac134f348817052322d2119))

## [1.29.0](https://github.com/myparcelnl/pdk/compare/v1.28.1...v1.29.0) (2022-12-12)


### :sparkles: New Features

* **actions:** add export return ([#53](https://github.com/myparcelnl/pdk/issues/53)) ([0bd6260](https://github.com/myparcelnl/pdk/commit/0bd62600dc7b60006846c8d75b9bf13e7232b6f7))

## [1.28.1](https://github.com/myparcelnl/pdk/compare/v1.28.0...v1.28.1) (2022-12-09)


### :bug: Bug Fixes

* **label:** set correct headers ([#50](https://github.com/myparcelnl/pdk/issues/50)) ([fa32ec5](https://github.com/myparcelnl/pdk/commit/fa32ec584416648b40079ed3c88b424ec053b074))

## [1.28.0](https://github.com/myparcelnl/pdk/compare/v1.27.0...v1.28.0) (2022-12-09)


### :sparkles: New Features

* **service:** add more methods to country service ([#58](https://github.com/myparcelnl/pdk/issues/58)) ([0ee9017](https://github.com/myparcelnl/pdk/commit/0ee9017e2742694b152572da1d5b235289f4c169))

## [1.27.0](https://github.com/myparcelnl/pdk/compare/v1.26.2...v1.27.0) (2022-12-08)


### :sparkles: New Features

* **repository:** pull up retrieve to base repository ([#60](https://github.com/myparcelnl/pdk/issues/60)) ([138c027](https://github.com/myparcelnl/pdk/commit/138c027386e8551104be2a90e78719163838594a))

## [1.26.2](https://github.com/myparcelnl/pdk/compare/v1.26.1...v1.26.2) (2022-12-08)


### :bug: Bug Fixes

* **model:** invalidate cast cache when setting attribute ([#63](https://github.com/myparcelnl/pdk/issues/63)) ([83b4434](https://github.com/myparcelnl/pdk/commit/83b4434cf59a4a83eed95ee924e61759596b3360))

## [1.26.1](https://github.com/myparcelnl/pdk/compare/v1.26.0...v1.26.1) (2022-11-30)


### :bug: Bug Fixes

* **returns:** return correct return shipment ([#56](https://github.com/myparcelnl/pdk/issues/56)) ([42a9ff7](https://github.com/myparcelnl/pdk/commit/42a9ff7f8e2e19f1c29fb1caef75f861c62a35f9))

## [1.26.0](https://github.com/myparcelnl/pdk/compare/v1.25.3...v1.26.0) (2022-11-18)


### :sparkles: New Features

* **shipments:** get by id ([#49](https://github.com/myparcelnl/pdk/issues/49)) ([fd8477e](https://github.com/myparcelnl/pdk/commit/fd8477eeeee03a64c67af4366a400570d7e41105))

## [1.25.3](https://github.com/myparcelnl/pdk/compare/v1.25.2...v1.25.3) (2022-11-11)


### :bug: Bug Fixes

* **actions:** cast orderId parameter to array ([#47](https://github.com/myparcelnl/pdk/issues/47)) ([11279e9](https://github.com/myparcelnl/pdk/commit/11279e99113bde08c01a56a39d36c191fbcab5d3))

## [1.25.2](https://github.com/myparcelnl/pdk/compare/v1.25.1...v1.25.2) (2022-11-10)


### :bug: Bug Fixes

* **actions:** get correct labels when printing order ([#48](https://github.com/myparcelnl/pdk/issues/48)) ([6cc88a9](https://github.com/myparcelnl/pdk/commit/6cc88a9e7123fda00ecbd4fe4ab9c4fcd854f0d3))

## [1.25.1](https://github.com/myparcelnl/pdk/compare/v1.25.0...v1.25.1) (2022-11-10)


### :bug: Bug Fixes

* **order:** add carrier when creating shipment ([#46](https://github.com/myparcelnl/pdk/issues/46)) ([eb24ce2](https://github.com/myparcelnl/pdk/commit/eb24ce2defeff4a88e8bcd05585793353807f86c))

## [1.25.0](https://github.com/myparcelnl/pdk/compare/v1.24.0...v1.25.0) (2022-11-08)


### :sparkles: New Features

* **actions:** add print order action ([#44](https://github.com/myparcelnl/pdk/issues/44)) ([aecef4e](https://github.com/myparcelnl/pdk/commit/aecef4ea25fd12a658df861a3ef9f6be9186a2b7))

## [1.24.0](https://github.com/myparcelnl/pdk/compare/v1.23.0...v1.24.0) (2022-11-01)


### :sparkles: New Features

* add validation to pdk orders ([#36](https://github.com/myparcelnl/pdk/issues/36)) ([0113b5e](https://github.com/myparcelnl/pdk/commit/0113b5edff7813e0ae2d49f971b79519fab47205))


### :bug: Bug Fixes

* validate shipments correctly ([#43](https://github.com/myparcelnl/pdk/issues/43)) ([2336727](https://github.com/myparcelnl/pdk/commit/2336727e7e5b2bd55aa40702e07c7146b824fe2a))

## [1.23.0](https://github.com/myparcelnl/pdk/compare/v1.22.0...v1.23.0) (2022-09-27)


### :sparkles: New Features

* add platform ([#41](https://github.com/myparcelnl/pdk/issues/41)) ([8af20ad](https://github.com/myparcelnl/pdk/commit/8af20ad606735667c871ac8ef5ff1738e1034e0d))

## [1.22.0](https://github.com/myparcelnl/pdk/compare/v1.21.0...v1.22.0) (2022-09-19)


### :sparkles: New Features

* add product settings ([#38](https://github.com/myparcelnl/pdk/issues/38)) ([ff3aa33](https://github.com/myparcelnl/pdk/commit/ff3aa335a46a4885f4d06d44fa8beaed206497e6))

## [1.21.0](https://github.com/myparcelnl/pdk/compare/v1.20.0...v1.21.0) (2022-09-16)


### :sparkles: New Features

* add delivery options config ([#39](https://github.com/myparcelnl/pdk/issues/39)) ([bdfca5c](https://github.com/myparcelnl/pdk/commit/bdfca5ce317fe7eb9fab1419985cb477338b78f8))

## [1.20.0](https://github.com/myparcelnl/pdk/compare/v1.19.0...v1.20.0) (2022-09-08)


### :sparkles: New Features

* add settings manager ([#37](https://github.com/myparcelnl/pdk/issues/37)) ([0e6f979](https://github.com/myparcelnl/pdk/commit/0e6f979d26f7b1035364ff355596725a365ed792))

## [1.19.0](https://github.com/myparcelnl/pdk/compare/v1.18.1...v1.19.0) (2022-09-01)


### :sparkles: New Features

* add plugin hooks ([#35](https://github.com/myparcelnl/pdk/issues/35)) ([930d7a2](https://github.com/myparcelnl/pdk/commit/930d7a2fd38e3e5a0305404f523b32b4fd87e7e6))

## [1.18.1](https://github.com/myparcelnl/pdk/compare/v1.18.0...v1.18.1) (2022-09-01)


### :bug: Bug Fixes

* **order:** fix order lines ([890c223](https://github.com/myparcelnl/pdk/commit/890c2233b88f341008779e68af5c287aa00631db))

## [1.18.0](https://github.com/myparcelnl/pdk/compare/v1.17.0...v1.18.0) (2022-08-31)


### :sparkles: New Features

* add fulfilment order repository ([#33](https://github.com/myparcelnl/pdk/issues/33)) ([9ab1e65](https://github.com/myparcelnl/pdk/commit/9ab1e65556cce8ff1e61a24cba5b60f22f46ee5e))

## [1.17.0](https://github.com/myparcelnl/pdk/compare/v1.16.0...v1.17.0) (2022-08-30)


### :sparkles: New Features

* add settings views and models ([#26](https://github.com/myparcelnl/pdk/issues/26)) ([10b77f2](https://github.com/myparcelnl/pdk/commit/10b77f27d72bf2810dfbe3e1cb6eb7df59e83af9))

## [1.16.0](https://github.com/myparcelnl/pdk/compare/v1.15.0...v1.16.0) (2022-08-24)


### :sparkles: New Features

* add user agent header ([#32](https://github.com/myparcelnl/pdk/issues/32)) ([10e29d4](https://github.com/myparcelnl/pdk/commit/10e29d4fe9f53cea513511b9d81c0a089fb57c84))

## [1.15.0](https://github.com/myparcelnl/pdk/compare/v1.14.0...v1.15.0) (2022-08-22)


### :sparkles: New Features

* **container:** change implementation to improve injection ([#31](https://github.com/myparcelnl/pdk/issues/31)) ([51ac114](https://github.com/myparcelnl/pdk/commit/51ac1142c701b58024e04a664cff357abf5d9bfb))

## [1.14.0](https://github.com/myparcelnl/pdk/compare/v1.13.0...v1.14.0) (2022-08-22)


### :sparkles: New Features

* add return shipments ([#27](https://github.com/myparcelnl/pdk/issues/27)) ([4b85c7e](https://github.com/myparcelnl/pdk/commit/4b85c7e9490ee774a1d85ffdd1a93f519d8eb447))

## [1.13.0](https://github.com/myparcelnl/pdk/compare/v1.12.0...v1.13.0) (2022-08-19)


### :sparkles: New Features

* add language service ([#29](https://github.com/myparcelnl/pdk/issues/29)) ([1425a7b](https://github.com/myparcelnl/pdk/commit/1425a7bd4fe95a3421f8701edb619ac85d509277))

## [1.12.0](https://github.com/myparcelnl/pdk/compare/v1.11.1...v1.12.0) (2022-08-16)


### :sparkles: New Features

* allow using dependency injection with constructors of manually set container items ([9e60e6d](https://github.com/myparcelnl/pdk/commit/9e60e6d7fb18f71c86ef2d9c9404e264fac2463e))

## [1.11.1](https://github.com/myparcelnl/pdk/compare/v1.11.0...v1.11.1) (2022-08-08)


### :bug: Bug Fixes

* fix null exception on creating carrier options ([33f26b2](https://github.com/myparcelnl/pdk/commit/33f26b2de688e6e61bc6efe2eec3e691538d56a2))

## [1.11.0](https://github.com/myparcelnl/pdk/compare/v1.10.1...v1.11.0) (2022-08-08)


### :sparkles: New Features

* add delivery date service ([#25](https://github.com/myparcelnl/pdk/issues/25)) ([68b6750](https://github.com/myparcelnl/pdk/commit/68b67501c899bf781b78034df8b99ff9373e4d85))

## [1.10.1](https://github.com/myparcelnl/pdk/compare/v1.10.0...v1.10.1) (2022-08-03)


### :bug: Bug Fixes

* **logger:** move facade to correct namespace ([99799b8](https://github.com/myparcelnl/pdk/commit/99799b8d4c8b7b63373fb56d2bb7d7081758fecc))

## [1.10.0](https://github.com/myparcelnl/pdk/compare/v1.9.2...v1.10.0) (2022-07-29)


### :sparkles: New Features

* add shipments ([#24](https://github.com/myparcelnl/pdk/issues/24)) ([ecb0294](https://github.com/myparcelnl/pdk/commit/ecb02943ce069e021bde71a4c5733170ece7013e))

## [1.9.2](https://github.com/myparcelnl/pdk/compare/v1.9.1...v1.9.2) (2022-07-27)


### :zap: Performance Improvements

* improve model attributes performance ([a84e1c7](https://github.com/myparcelnl/pdk/commit/a84e1c79cb767c6e156d2ad34972334eeda00292))

## [1.9.1](https://github.com/myparcelnl/pdk/compare/v1.9.0...v1.9.1) (2022-07-27)


### :zap: Performance Improvements

* improve model toArray performance ([bc5c048](https://github.com/myparcelnl/pdk/commit/bc5c048167ff0eba0d3fa2b91da6c07391749888))

## [1.9.0](https://github.com/myparcelnl/pdk/compare/v1.8.0...v1.9.0) (2022-07-26)


### :sparkles: New Features

* carrier object ([#21](https://github.com/myparcelnl/pdk/issues/21)) ([50a6778](https://github.com/myparcelnl/pdk/commit/50a67786a32d876cac334ecf2637b65906e42554))

## [1.8.0](https://github.com/myparcelnl/pdk/compare/v1.7.2...v1.8.0) (2022-07-21)


### :sparkles: New Features

* **collection:** add cast property for collection items ([#23](https://github.com/myparcelnl/pdk/issues/23)) ([4b821af](https://github.com/myparcelnl/pdk/commit/4b821af0d58d1f2ffedf5bce3f60c5fea03a01fe))

## [1.7.2](https://github.com/myparcelnl/pdk/compare/v1.7.1...v1.7.2) (2022-07-21)


### :bug: Bug Fixes

* **model:** fix validation error on passing wrongly cased property in constructor ([#22](https://github.com/myparcelnl/pdk/issues/22)) ([785e654](https://github.com/myparcelnl/pdk/commit/785e654a3aa9ef28cb09edd6414d0d99c4bc025a))

## [1.7.1](https://github.com/myparcelnl/pdk/compare/v1.7.0...v1.7.1) (2022-07-21)


### :bug: Bug Fixes

* **model:** improve model casting ([#20](https://github.com/myparcelnl/pdk/issues/20)) ([4a58098](https://github.com/myparcelnl/pdk/commit/4a58098fa51cc537943c68550b9dc0c464a1c049))

## [1.7.0](https://github.com/myparcelnl/pdk/compare/v1.6.0...v1.7.0) (2022-07-20)


### :sparkles: New Features

* add logger ([#19](https://github.com/myparcelnl/pdk/issues/19)) ([16c5f1c](https://github.com/myparcelnl/pdk/commit/16c5f1cb722667e956e848c2c2de765083f9310b))

## [1.6.0](https://github.com/myparcelnl/pdk/compare/v1.5.0...v1.6.0) (2022-07-19)


### :sparkles: New Features

* add facades ([#18](https://github.com/myparcelnl/pdk/issues/18)) ([1039f3e](https://github.com/myparcelnl/pdk/commit/1039f3e1fe570ae79d69400d065791477157e2bc))

## [1.5.0](https://github.com/myparcelnl/pdk/compare/v1.4.1...v1.5.0) (2022-07-18)


### :sparkles: New Features

* **model:** only allow defined attributes to be set ([#17](https://github.com/myparcelnl/pdk/issues/17)) ([6e54bbb](https://github.com/myparcelnl/pdk/commit/6e54bbbb8f4c3710c3760e2970ea6ea2bbca146a))

## [1.4.1](https://github.com/myparcelnl/pdk/compare/v1.4.0...v1.4.1) (2022-07-14)


### :bug: Bug Fixes

* **model:** fix wrong attributes being set from cast cache ([a25f06d](https://github.com/myparcelnl/pdk/commit/a25f06dc8be6032420cfe542f893716a12e482a5))

## [1.4.0](https://github.com/myparcelnl/pdk/compare/v1.3.0...v1.4.0) (2022-07-14)


### :sparkles: New Features

* add delivery options merger ([#16](https://github.com/myparcelnl/pdk/issues/16)) ([92d531b](https://github.com/myparcelnl/pdk/commit/92d531bca5ff056ac002dcb96bc906241a60fa22))

## [1.3.0](https://github.com/myparcelnl/pdk/compare/v1.2.0...v1.3.0) (2022-07-14)


### :bug: Bug Fixes

* fix toArray on nested collections ([31f3258](https://github.com/myparcelnl/pdk/commit/31f3258da2cc6e0cd5bd4a20181ff52268c29a59))
* snake case all attributes on toArray ([672a7b3](https://github.com/myparcelnl/pdk/commit/672a7b3d054410a48d1f9e119403124776d009cc))


### :sparkles: New Features

* add casts property to model ([#14](https://github.com/myparcelnl/pdk/issues/14)) ([bdd0269](https://github.com/myparcelnl/pdk/commit/bdd02694eca56a7dc7515e59bc023bbcc4c30c9a))
* add classes for shipments ([8d296e3](https://github.com/myparcelnl/pdk/commit/8d296e33eaaa2626626a521aab4b6909cf340d04))
* allow using model properties with any casing ([d25bb8a](https://github.com/myparcelnl/pdk/commit/d25bb8a063144cca3537b25eaa823ffe69267bf6))

## [1.2.0](https://github.com/myparcelnl/pdk/compare/v1.1.0...v1.2.0) (2022-07-04)


### :sparkles: New Features

* add repositories and requests ([#5](https://github.com/myparcelnl/pdk/issues/5)) ([03b23f1](https://github.com/myparcelnl/pdk/commit/03b23f1710f36984497c0ad2ade7bb0c68b2a14a))

## [1.1.0](https://github.com/myparcelnl/pdk/compare/v1.0.1...v1.1.0) (2022-05-31)


### :sparkles: New Features

* add convert to digital stamp weight ([#2](https://github.com/myparcelnl/pdk/issues/2)) ([18eb250](https://github.com/myparcelnl/pdk/commit/18eb2503c9b51425a5b7c8f196d365a953b1beaa))

### [1.0.1](https://github.com/myparcelnl/pdk/compare/v1.0.0...v1.0.1) (2022-04-06)


### :bug: Bug Fixes

* calculate the weights correctly ([#1](https://github.com/myparcelnl/pdk/issues/1)) ([5975c2a](https://github.com/myparcelnl/pdk/commit/5975c2a059f66a6fd517b2dc5de2cbc03fc9ab1b))

## 1.0.0 (2022-03-25)


### :sparkles: New Features

* add service for weight conversion ([0af5e04](https://github.com/myparcelnl/pdk/commit/0af5e045d04070c2c00168600018d43333b97312))
