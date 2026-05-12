# AGENTS.md

This repository follows the AUI Development Framework.

## Operating Instructions

- Read `README.md` and the relevant `docs/use-cases/` file before changing behavior.
- Keep use cases, Related Code sections, and tests synchronized.
- Add issue drafts for missing test coverage instead of leaving gaps implicit.
- Use `docs/templates/` for new use cases, issue drafts, and decision records.
- Prefer repository-relative paths and portable configuration.
- Do not use absolute symlinks for shared code.
- Keep debug output quiet by default and controlled by settings.
- Run `make check` before committing when the local toolchain is available.

## Repository Layers

- `AGENTS.md`: persistent AUI instructions for Codex and future agents.
- `.github/`: issue, pull request, discussion, and CI templates.
- `docs/templates/`: reusable documentation templates.
- `scripts/`: repeatable automation commands.
- `Makefile`: one-command execution.
- `.cursor/rules/` and `.codex/prompts/`: IDE-integrated AUI context.
- `.github/workflows/`: automated enforcement.

