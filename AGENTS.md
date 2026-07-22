# RISE GATE repository workflow

## Git operations

- After completing and validating a coherent requested change, Codex must commit the related files and push the current branch to `origin` without requiring a separate user request.
- Stage only files related to the current task. Never stage or overwrite unrelated user changes.
- If validation, authentication, conflicts, or scope are unclear, report the blocker instead of forcing the operation.

## Deployment operations

- Git push and server deployment are separate operations.
- A push must not automatically deploy to demo or production.
- Deployment workflows must use `workflow_dispatch` only; do not configure a deployment trigger on `push`.
- The user manually runs deployments from GitHub Actions. Codex prepares and explains the workflow but does not run it unless the user explicitly overrides this rule for that deployment.
- Preserve server-owned uploads, inquiries, sessions, environment files, databases, and generated content.
- Store credentials in GitHub Actions secrets. Never commit or display secrets.

## Project-specific configuration

- Application: lightweight PHP with JSON content storage.
- Validation: run PHP syntax checks for every changed PHP file and `git diff --check`.
- Demo URL: `https://demo.prohit-okinawa.com/`
- Production URL: `https://prohit-okinawa.com/`
- Demo workflow: `.github/workflows/deploy-demo.yml` (`デモへデプロイ`).
- Production workflow: not configured yet.
- Protected server paths: `/prohit-okinawa.com/storage-demo/`, runtime uploads, `.user.ini`, deployment sync state, and server-created sessions or inquiries.
