# StoryBranch - Claude Guidelines

## Project Overview
Multi-user branching narrative platform with Duolingo-style UI (Blue theme). Users can create story universes with multiple timelines, visualize branching narratives, and edit scenes with rich text.

## Tech Stack
- **Frontend**: Nuxt 3 (Vue.js)
- **Styling**: Tailwind CSS + Custom Duolingo components
- **Database**: MySQL 9.2.0
- **ORM**: Prisma
- **Auth**: Email/Password (JWT sessions)
- **Graph Viz**: Vue Flow
- **Rich Editor**: TipTap
- **Font**: Nunito (Google Fonts)

## Key Files
- `PLAN.md` - Full project plan, database schema, folder structure, API endpoints
- `CHECKLIST.md` - Development progress tracker (16 phases, 179 tasks)
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
User, Session, Universe, Character, Timeline, Scene, SceneCharacter, Tag, SceneTag

## API Pattern
```
/api/auth/*           - Authentication
/api/universes/*      - Universe CRUD
/api/timelines/*      - Timeline CRUD
/api/scenes/*         - Scene CRUD
/api/characters/*     - Character management
/api/tags/*           - Tag management
/api/import/raw       - Import from raw.md
/api/search           - Full-text search
```

## Development Guidelines
1. Follow Nuxt 3 conventions (server/api for endpoints, composables for state)
2. Use Prisma for all database operations
3. Maintain 3D button/card effect for Duolingo aesthetic
4. Store rich text as HTML/JSON from TipTap
5. Branch system: scenes can be marked as branch points, spawning alternate timelines

## Current Status
Phase 1 (Foundation) - Not started. See CHECKLIST.md for detailed progress.
