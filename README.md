# StoryBranch

Multi-user branching narrative platform with Duolingo-style UI.

## Overview

StoryBranch is a web application where users can:
- Create story universes with multiple timelines
- Visualize branching narratives with an interactive graph
- Edit scenes with a rich text editor
- Create "what if" alternate branches from any decision point
- Search and filter by tags, characters, mood, and more

## Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | Nuxt 3 (Vue.js) |
| Styling | Tailwind CSS + Custom Duolingo components |
| Database | MySQL 9.2.0 |
| ORM | Prisma |
| Auth | Email/Password (JWT sessions) |
| Graph Viz | Vue Flow |
| Rich Editor | TipTap |
| Font | Nunito (Google Fonts) |

## Color Palette

- **Primary Blue**: `#1CB0F6`
- **Primary Dark**: `#1899D6` (3D shadow)
- **Primary Light**: `#DBEAFE`
- **Success Green**: `#58CC02`

## Getting Started

### Prerequisites

- Node.js 18+
- MySQL 9.2.0
- pnpm (recommended) or npm

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/manyunyu7/amankilla.git
   cd amankilla
   ```

2. Install dependencies:
   ```bash
   pnpm install
   ```

3. Set up environment variables:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. Set up the database:
   ```bash
   npx prisma migrate dev
   npx prisma generate
   ```

5. Run the development server:
   ```bash
   pnpm dev
   ```

6. Open [http://localhost:3000](http://localhost:3000)

## Project Structure

```
/amankila/
├── prisma/           # Database schema and migrations
├── server/
│   ├── api/          # API endpoints
│   ├── middleware/   # Auth middleware
│   └── utils/        # Helpers (db, parser, auth)
├── components/
│   ├── ui/           # Duolingo-style components (DButton, DCard, etc.)
│   ├── graph/        # Timeline visualization
│   ├── editor/       # TipTap rich text editor
│   └── layout/       # App layout components
├── composables/      # Vue composables
├── pages/            # Nuxt pages
├── assets/css/       # Styles
└── utils/            # Client utilities
```

## UI Components

All UI components use the "D" prefix (Duolingo-style):
- `DButton` - 3D buttons with shadow effect
- `DCard` - Cards with depth
- `DInput`, `DBadge`, `DToggle`, `DToast`, `DModal`, `DBottomSheet`

## API Endpoints

### Auth
- `POST /api/auth/register` - Create account
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Current user

### Universes
- `GET /api/universes` - List universes
- `POST /api/universes` - Create universe
- `GET /api/universes/:id` - Get universe
- `PUT /api/universes/:id` - Update universe
- `DELETE /api/universes/:id` - Delete universe

### Timelines & Scenes
- `GET /api/timelines/:id` - Get timeline with scenes
- `POST /api/scenes/:id/branch` - Create branch from scene

### Import & Search
- `POST /api/import/raw` - Import story data
- `GET /api/search` - Full-text search

## Development Roadmap

- **Phase 1**: Core (Auth, CRUD, Basic Graph, Import)
- **Phase 2**: Branch System (Branch points, Alternate timelines)
- **Phase 3**: Organization (Characters, Tags, Search)
- **Phase 4**: Social (Public universes, Forking, OAuth)
- **Phase 5**: Polish (Mobile, Animations, Export)

## License

MIT
