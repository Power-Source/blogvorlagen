# New Blog Templates

[Deutsch](README.md) | **English**

[![Version](https://img.shields.io/badge/Version-1.0.0-2271b1?style=flat-square)](readme.txt)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb4?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-up%20to%207.1.4-21759b?style=flat-square&logo=wordpress&logoColor=white)
![ClassicPress](https://img.shields.io/badge/ClassicPress-2.7.4-03768e?style=flat-square)
[![License](https://img.shields.io/badge/License-GPL--3.0--or--later-2ea44f?style=flat-square)](https://www.gnu.org/licenses/gpl-3.0.html)

**Create new websites without starting from scratch every time.**

With New Blog Templates, prepare a website once and then use it as the starting point for more websites in your network. For example, club sites, project blogs, or school websites can start with suitable pages, posts, and a consistent look and feel.

## What can you copy?

Choose which parts of each template new websites will receive:

- Settings, the active theme, and active plugins
- Posts and selected pages
- Categories, tags, and links
- Menus and uploaded files
- Optionally, users from the template website

You can also set a default template, organize templates into categories, and offer new users a selection during registration.

![Overview of a sample template with the Edit, Delete, and Set as default actions](docs/images/08-vorlagenuebersicht.png)

## What do you need?

- A WordPress or ClassicPress multisite network
- Super Administrator access to the **Network Admin**
- A separate website to prepare as a template

This plugin is not intended for a single WordPress or ClassicPress website without a network.

## Get started

1. Install the plugin and select **Network Activate** under **Plugins** in Network Admin.
2. Prepare a separate template website with the content and theme you want.
3. Go to **Blog Templates → Blog Templates**, name the template, and select the prepared website.
4. Create the template, choose what to copy, and click **Save Template**.
5. Go to **Sites → Add New**, choose your template, and create the new website.

**[Read the detailed guide with screenshots (currently in German) →](docs/README.md)**

## Good to know

- **A template is a starting point, not ongoing synchronization.** Changes to the template website apply to websites created later. Existing websites remain unchanged.
- **Use a separate template website.** The network's main website is not intended to be used as a template source.
- **Only copy content that should be shared.** Check comments, files, and users beforehand, too.
- **New websites keep their own address and title.**
- **The plugin is not a backup solution.** Create a backup before making major changes.

## Help and feedback

Find answers to frequently asked questions at the end of the [German guide](docs/README.md#häufige-fragen).

Report bugs or suggest improvements in the [GitHub support area](https://github.com/cp-psource/blogvorlagen/issues).

---

A plugin by [PSOURCE](https://github.com/cp-psource).
