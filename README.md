# Unity for WP

Stable tag: 2.0

**WordPress Plugin for Unity WebGL Projects**

## Overview

**Unity for WP** is a WordPress plugin that simplifies the integration of Unity WebGL builds into your WordPress site. It automates the setup process and allows you to easily upload, manage, and initialize multiple Unity WebGL builds.

The plugin handles everything—your job is to build your Unity WebGL project and upload the zipped build through the plugin interface.

**Important:**  
Make sure you zip the contents of your build folder directly, **not the parent folder**. The system will not work if an extra directory wraps your build files.

**Example of how to zip your build:**  
[Build Example](https://prnt.sc/EqE7U-nJJeQ9)

---

## Features

- **Multiple Build Support**: Upload and manage multiple Unity WebGL builds on the same site.
- **Automatic Folder Structure**: Each uploaded build is extracted into its own directory within the `unitybuild` folder (e.g., `ubuild_1`, `ubuild_2`, etc.).
- **Build Management**: View all uploaded builds in the **Builds** tab under the plugin settings. You can delete or re-upload individual builds at any time.
- **Shortcode Support**: Embed Unity builds using a simple shortcode with customizable options.

---

## How to Use

### Uploading Your Build

1. Create a Unity WebGL build.
2. Zip the build files directly (do **not** include the parent folder).
3. Upload the zipped build through the plugin’s interface.
4. The plugin will extract the build and assign it a unique ID (e.g., `ubuild_1`).

---

## Shortcode Usage

To embed a Unity WebGL build, use the following shortcode: `[unity id="ubuild_1"]`

### Shortcode Parameters

- `id` (required): Specifies the build ID to embed.
- `autostart` (optional): If set to `false`, the Unity WebGL project will only start when a button is clicked.
- `width` and `height` (optional): Customize the size of the Unity WebGL canvas. You can use pixels or percentages.

**Examples:** `[unity id="ubuild_1" autostart="false" width="100%" height="50vh"]`

---

## Custom Button and Placeholder

If `autostart` is set to `false`, you can provide a custom button text and a placeholder image in the plugin settings. Once the user clicks the button, the placeholder and button will disappear, and the Unity WebGL project will initialize.

---

## JavaScript Event Listener

The plugin provides a custom event listener that fires once the Unity instance is initialized.

**Example:**

```javascript
document.addEventListener("unityInitialized", function (e) {
  // e.detail.unityInstance contains the Unity instance
  // e.detail.buildId contains the ID of the initialized build
  console.log("Unity initialized:", e.detail);
});
```

## Future Plans

- Dedicated GitHub Repository for jslibs, editor extensions, and demo builds.
- Scene Loading Events: Code samples to handle scene load events will be added.
- Enhanced Build Management Interface.

## Troubleshooting

- Ensure your server supports Gzip or Brotli compression for optimal performance.
- If compressed builds are not working, switch to an uncompressed version in the plugin settings.

## Changelog

2.0 - 2025.02.16

- Multiple Build support
- New interface for your uploaded builds
- New upload function within your wpadmin (The build does not need to be uploaded via ssh or ftp)
- New autostart enable disable option for the webgls
- New shortcodes to support multiple builds
- New shortcode paramteres: autostart, width, height
- Option to set button and placeholder images in your canvas if autostart false
- Custom js eventlistener for the developers: _unityInitialized_

- Removed the sample build

---

1.1 - 2024.09.24

- Fixed the Deactivation issue
- Added the plugin to the update server
- **New settings:** Set your Canvas height,width, and aspect ration
- **New settings option:** You can enable the unfiltered files upload only for the admin for this file types: obj, gltf+json, gltf-binary, mtl
- New Js eventlisner added when the Unity finished the initialization

---

1.0
Initial release
