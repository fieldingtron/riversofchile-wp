# riversofchile
riversofchile website wordpress coding

## Local env workflow

- Keep `.env` local only (already gitignored).
- Commit `.env.encrypted` instead of raw secrets.
- Create a local env file after clone with `./scripts/post-install.sh`.
- Re-encrypt after updating `.env` with `./scripts/env-sync.sh encrypt`.

If `PASSWORD` is not set in your shell, the scripts prompt for it securely.
