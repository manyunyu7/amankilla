# StoryBranch - Claude Guidelines

## Project Overview
Multi-user branching narrative platform with Duolingo-style UI (Blue theme). Users can create story universes with multiple timelines, visualize branching narratives, and edit scenes with rich text.

## Tech Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3.5+ with Inertia.js
- **Styling**: Tailwind CSS 4.0
- **Database**: MySQL 9.2.0
- **Auth**: Laravel Sanctum + Laravel Breeze
- **Build**: Vite 7.0
- **State**: Pinia
- **Graph Viz**: Vue Flow
- **Rich Editor**: TipTap
- **Font**: Nunito (Google Fonts)
- **Routing**: Ziggy (Laravel routes in JS)

## Key Files
- `PLAN.md` - Full project plan, database schema, folder structure, API endpoints
- `CHECKLIST.md` - Development progress tracker
- `raw.md` - Original story data to be parsed and imported

## Color Palette (Duolingo Blue)
```
Primary Blue: #1CB0F6
Primary Dark: #1899D6 (3D shadow)
Primary Light: #DBEAFE
Success Green: #58CC02
```

## Component Naming
All UI components use "D" prefix (Duolingo-style):
- `DButton`, `DCard`, `DInput`, `DBadge`, `DToggle`, `DToast`, `DModal`, `DBottomSheet`

## Database Models
User, Universe, Character, Timeline, Scene, SceneCharacter, Tag, SceneTag

## API Pattern (Laravel Controllers)
```
/login, /register       - Authentication (Breeze)
/api/universes/*        - Universe CRUD
/api/timelines/*        - Timeline CRUD
/api/scenes/*           - Scene CRUD
/api/characters/*       - Character management
/api/tags/*             - Tag management
/api/import/raw         - Import from raw.md
/api/search             - Full-text search
```

## Development Guidelines
1. Follow Laravel conventions (Controllers, Models, Migrations, Requests)
2. Use Inertia.js for SPA-like experience with Vue components
3. Use Eloquent ORM for all database operations
4. Maintain 3D button/card effect for Duolingo aesthetic
5. Store rich text as HTML/JSON from TipTap
6. Branch system: scenes can be marked as branch points, spawning alternate timelines

## Development Commands
```bash
# Start all services (server, queue, logs, vite)
composer dev

# Run tests
composer test

# Fresh install
composer setup
```

## Current Status
Phase 1 (Foundation) - Not started. See CHECKLIST.md for detailed progress.
