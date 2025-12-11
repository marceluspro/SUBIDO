## 3.3.2 - 2025-09-01

### Changed

* Use `Module_Core::config()` method instead of constants

## 3.3.1 - 2024-11-13

### Changed

* Re-try the register request in case it fails due to invalid `woosa_secret`

## 3.3.0 - 2024-09-10

### Added

* New header `x-woosa-plugin-name` which contain the plugin name and major version

## 3.2.1 - 2024-08-23

### Changed

* Return `WP_Error` in case WC API user or credentials could not be created

## 3.2.0 - 2024-08-08

### Changed

* Control the Heartbeat module actions based on the new hook

## 3.1.3 - 2024-04-03

### Fixed

* The authorization types are sent in headers instead of body

## 3.1.2 - 2024-02-14

### Changed

* Restrict the auhtoization when the shop is not registered

## 3.1.1 - 2024-01-30

### Fixed

* In case the shop instance has changed then do not include `woosa_secret` in the request of shop registration

## 3.1.0 - 2024-01-25

### Added

* Send notification to add/remove cron job from our cron UI

## 3.0.0 - 2023-09-14

### Changed

* Modify the shop registration logic to use Settigns module v2
* Use JS variable from Core module
* The argument `$use_service` for method `Module_Midlayer::base_url()` it's used only to add the service name but not the prefix `woocommerce` anymore
* Send plugin heartbeat request via Worker action

## 2.1.4 - 2023-07-19

### Fixed

* The shop registration file path checks is not removing the backslashes which leads to not displaying the shop register button

## 2.1.3 - 2023-07-07

### Fixed

* The template for the registering shop is not displayed due to wrong directory separator support on Windows OS

## 2.1.2 - 2023-06-21

### Changed

* Improved the error message at shop registration

## 2.1.1 - 2023-05-04

### Added

* Insert the Milayer IPs in the Tools settings section

### Changed

* Adjust the fecvency of hearbeat requests to be daily and at a random hour
* Remove the parameter `domain` from the request on `/regiter` endpoint

## 2.1.0 - 2023-04-13

### Added

* Send request to `/woocommerce/plugin-heartbeat` endpoint hourly

### Fixed

* Midlayer might return error at revoking authorization, in that chase the shop still remains marked as authorized

### Changed

* In case the permalinks are set on `Plain` a warning message will be displayed to inform the shop owner

## 2.0.4 - 2022-11-17

* [TWEAK] - Make sure the domain is always lower cases

## 2.0.3 - 2022-06-09

* [TWEAK] - Add request header `x-woosa-plugin-slug`
* [TWEAK] - Change keyword `_wsa_settings_tab_id_` to `_wsa_midlayer_service_` in `Module_Midlayer::service()`
* [TWEAK] - Send notification when upgrade event is triggered

## 2.0.2 - 2022-05-05

* [FIX] - Sending activation state in the same process of registration it fails due to invalid signature
* [TWEAK] - Use template hook to replace the autorization section instead of WC settings hook