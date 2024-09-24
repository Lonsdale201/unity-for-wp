# unity-for-wp
Simple loader and inicilizer plugin for Wordpress + Unity + WebGL project

## ENG description

**Instructions:**

Copy your Unity build files into the unitybuild folder.
File names do not matter, as the plugin automatically detects them. However, the build files must be placed inside the Build folder.
The plugin supports loading both Gzip and Brotli formats. You can toggle between these formats in the plugin settings.

*This is a standard WordPress plugin, simply install it in zip format after downloading the project from github.*

**Settings:**
You can access the plugin settings in WordPress under:
WP Admin > Settings > Unity WebGL

The plugin includes a demo build from Unity as a sample.
Use the shortcode to embed the Unity WebGL project:

`[unity]`

The initialization JavaScript file is only loaded when the shortcode is present on the page.
Additionally, there is a demo script located in the assets/samples folder.

**Example Usage:**

* Place a button using any page builder or native HTML and assign it the following ID: button1.
* In the demo scene, there is a cube. Clicking the button will change the cube's color to a random color.
  
**Camera Controls:**

* Rotate the camera by holding the left mouse button.
* Move the camera using the traditional WASD keys.
* Zoom in and out with the mouse scroll wheel.

**Troubleshooting:**

If your server cannot properly handle compressed builds (Gzip or Brotli), switch the plugin settings to use the uncompressed format.

## MAGYAR leírás

Ez a bővítmény a Unity-ban készített webGL projekteket képes betölteni. (egy időben csak egyet)
Ez egy hagyományos WordPress bővítmény, telepítsd egyszerűen zip formátumban, miután letöltötted a projektet innen a githubról.


a build fájlokat bele kell másolnod a(z) unitybuild nevű mappába. A fájl név nem számít, mert automatikusan felismeri őket, de a cél fájlok a Build mappában kell, hogy legyenek. 
Támogatja a Gzip és Brutti formátumot is betöltésnél. Ezt a bővítmény beállításaiban tudod átállítani.

*wpadmin/settings/unity webGL*

 A bővítmény tartalmaz egy demo build-et unity ből.

A bővítmény shortcode segítségével hívjba be az Unity webGL-t
A kapcsolódó inicializációs js fájl-t csak akkor tölti be ah az oldalon a shortcode meg van adva.
Az assets/samples mappában van egy demó script. A shortcode mellé helyezz el egy gombot (bármilyen page builder), vagy natív html segítségével. A gombnak adj meg ezt az ID-t: button1

A demó jelenetben egy kocka van elhelyezve, a gomb segítségével random tudod színezni.

A demóban ezen kívül tudod a kamerát forgatni, amíg lenyomva tartod a bal egérgombot.
A hagyományos WASD-el tudod mozgatni a kamerát, görgővel zoomolhatsz

Fontos, a build-et mindig a plugin mappájában található unitybuild mappába helyezd el
A fájlokat mindig így fogja betölteni: *\unitybuild\Build*

hogy milyen néven van prefixelve a fájl nem számít, de ne használj space-t, mert úgy nem képes megfelelően betölteni.

**Figyelj oda hogy kompresszált buildet nem biztos hogy a szervered képes megfelelően kezelni, ilyenkor állítsd vissza a beállítást tömörítetlen verzióban.**

Shortcode használata: `[unity]`

## Changelog

1.1 - 2024.09.24

* Fixed the Deactivation issue
* Added the plugin to the update server
* **New settings:** Set your Canvas height,width, and aspect ration
* **New settings option:** You can enable the unfiltered files upload only for the admin for this file types: obj, gltf+json, gltf-binary, mtl
* New Js eventlisner added when the Unity finished the initialization


1.0
Initial release
