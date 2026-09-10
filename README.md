# Model Merger

Web front end for merging two versions of an [SBML](https://sbml.org/) model. It
talks to the [BiVeS](https://sems.uni-rostock.de/projects/bives/) difference and
merge service through a small PHP wrapper (`public/bives/`), and renders the
structural diff as an interactive graph via the bundled **DiVil** submodule.

Two modes:

- **Automatic** (`/`) – upload two models, BiVeS merges them, download the result.
- **Semi-automatic** (`/user`) – step through the individual changes and pick a
  version for each, with a graph and per-list view of species, reactions,
  parameters, units, rules and functions.

Deployed at <https://merge-proto.bio.informatik.uni-rostock.de/>.

## Requirements

- Node.js 24 (see `.nvmrc`; `nvm use` picks it up). Node ≥ 20.19 also works.
- [Docker](https://docs.docker.com/) with Compose – only for running the BiVeS
  backend locally (see below).

## Setup

```sh
git clone --recurse-submodules <repo-url>
# or, in an existing clone:
git submodule update --init

npm install
cp .env.example .env      # then edit DOCKER_UID / DOCKER_GID to your `id -u` / `id -g`
```

The `DiVil` submodule (graph rendering) is required – the build fails without it.
`.env` (git-ignored) is read by both Vite and Docker Compose.

## Development

The merge features need a BiVeS backend. The public instance
(`https://merge-proto.bio.informatik.uni-rostock.de`) is often unavailable, so
the repo ships a local one via Docker Compose. `BIVES_PROXY` in `.env` is the
target the dev server proxies `/bives/*.php` to.

### Everything in Docker

```sh
docker compose up            # http://localhost:5173
docker compose down
```

`docker-compose.yml` brings up:

| service     | port             | what it is                                     |
| ----------- | ---------------- | ---------------------------------------------- |
| `web`       | `127.0.0.1:5173` | Vite dev server (`npm run dev` in a container) |
| `bives`     | `127.0.0.1:1234` | BiVeS web service (`binfalse/bives-webapp`)    |
| `bives-php` | `127.0.0.1:8088` | the `public/bives/*.php` merge wrappers        |

The `web` container bind-mounts the repo and hot-reloads. It runs as the uid/gid
in `.env` (`DOCKER_UID` / `DOCKER_GID`) so files it writes into the tree
(`node_modules`, `public/mathjax`) stay yours – set them to your `id -u` / `id -g`.

### Dev server on the host

Run just the backend in Docker and Vite locally:

```sh
docker compose up -d bives bives-php
npm run dev
```

`.env` already points `BIVES_PROXY` at `http://localhost:8088`. To target the
production host instead, put `BIVES_PROXY=https://merge-proto.bio.informatik.uni-rostock.de`
in `.env.local` (git-ignored).

## Build

```sh
npm run build      # output in dist/
npm run preview    # serve the production build on :5050
```

`predev` / `prebuild` run `scripts/copy-mathjax.mjs`, which vendors the MathJax 4
SVG component into `public/mathjax/` (git-ignored) from `node_modules`.

## Deploy

```sh
npm run deploy     # builds and publishes dist/ to GitHub Pages (gh-pages)
```

## Code style

```sh
npm run lint       # ESLint (flat config in eslint.config.js), autofix
npm run format     # Prettier over src/
```

## Layout

```
src/               Vue 3 application
  components/       SimpleMerge, UserMerge, Slider, Merger, ...
  composables/      xmlInteraction, mathjax (on-demand typesetting)
  router/
public/bives/      PHP wrappers that call the BiVeS service
DiVil/             submodule – SBGN graph rendering (d3)
docker-compose.yml local BiVeS backend
scripts/           build helpers
```
