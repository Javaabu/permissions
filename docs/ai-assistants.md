---
title: AI assistants
sidebar_position: 1.35
---

The docs are plain Markdown, written for people and AI coding assistants alike. The package ships them, so they always match the version you have installed.

| File | What |
| --- | --- |
| `vendor/javaabu/permissions/llms.txt` | The rules for using the package, and every page |
| `vendor/javaabu/permissions/docs/` | The pages |
| `vendor/javaabu/permissions/resources/boost/` | The Laravel Boost skill and guideline |

# Laravel Boost

[Laravel Boost](https://github.com/laravel/boost) finds the skill and guideline by itself in Composer packages. Run this after installing or updating the package:

```bash
php artisan boost:update
```

Choose `javaabu/permissions` if Boost asks which third-party packages to use.

# Other assistants

Point your assistant at the docs from the project's instructions file (`AGENTS.md`, `CLAUDE.md`, `.cursor/rules`, `.github/copilot-instructions.md`):

```md
## Permissions

Roles, permissions and policies use javaabu/permissions. Before adding a permission, policy or role, read vendor/javaabu/permissions/llms.txt.
```

Or copy `vendor/javaabu/permissions/resources/boost/skills/javaabu-permissions` into your assistant's skills folder, such as `.claude/skills/`.
