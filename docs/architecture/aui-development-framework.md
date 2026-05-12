# AUI Development Framework

The AUI Development Framework is a persistent development architecture for WordPress plugins. Its job is to keep intent, implementation, verification, and future work visible inside the repository.

## Core Records

- `README.md` explains the plugin purpose, setup, and main workflows.
- `AGENTS.md` persists Codex-facing development instructions.
- `.github/` templates standardize issues, pull requests, discussions, and CI.
- `docs/templates/` provides reusable use case, issue, and ADR templates.
- `docs/use-cases/` describes user-facing and admin-facing behavior.
- Each use case includes acceptance criteria and a Related Code section.
- `docs/github-issue-drafts/` stores issue-ready drafts for missing tests or known gaps.
- `docs/decisions/` records architecture decisions that future maintainers should not have to rediscover.
- `scripts/` and `Makefile` provide repeatable one-command checks.
- `.cursor/rules/` and `.codex/prompts/` keep IDE-integrated AUI context close to the code.
- `tests/` verifies behavior that matters to the use cases.

## Maintenance Loop

1. Capture or update the relevant use case.
2. Map the PHP functions, classes, hooks, and assets that implement it.
3. Identify missing automated tests.
4. Implement the smallest safe code change.
5. Add or update tests.
6. Update documentation while the context is fresh.
7. Commit one coherent unit of work.

## Repository Rules

- Prefer portable paths and repository-relative configuration.
- Avoid absolute symlinks for shared code.
- Keep shared infrastructure in its own plugin or package.
- Keep debug behavior gated by configuration so production output stays quiet.
- Keep local editor settings separate from shared workspace settings.
