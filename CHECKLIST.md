# StoryBranch - Development Checklist

> Track progress for building the branching narrative platform

---

## Phase 1: Foundation

### Project Setup
- [ ] Initialize Nuxt 3 project
- [ ] Install dependencies (Prisma, TipTap, Vue Flow, etc.)
- [ ] Configure Tailwind CSS
- [ ] Setup Nunito font (Google Fonts)
- [ ] Create folder structure
- [ ] Setup environment variables (.env)

### Database
- [ ] Create MySQL database `storybranch`
- [ ] Configure Prisma with MySQL
- [ ] Create schema (User, Universe, Timeline, Scene, etc.)
- [ ] Run initial migration
- [ ] Test database connection

### Design System (Duolingo Blue)
- [ ] Define color palette (colors.ts)
- [ ] Create DButton component (3D effect)
- [ ] Create DCard component (3D effect)
- [ ] Create DInput component
- [ ] Create DBadge component
- [ ] Create DToggle component
- [ ] Create DToast component
- [ ] Create DModal component
- [ ] Create DBottomSheet component
- [ ] Test all components in a sandbox page

---

## Phase 2: Authentication

### Backend
- [ ] Setup password hashing (bcrypt)
- [ ] Setup JWT token generation
- [ ] Create `/api/auth/register` endpoint
- [ ] Create `/api/auth/login` endpoint
- [ ] Create `/api/auth/logout` endpoint
- [ ] Create `/api/auth/me` endpoint
- [ ] Create auth middleware

### Frontend
- [ ] Create `useAuth` composable
- [ ] Create register page (`/register`)
- [ ] Create login page (`/login`)
- [ ] Setup auth state management
- [ ] Protect routes (middleware)
- [ ] Add logout functionality
- [ ] Handle auth errors gracefully

---

## Phase 3: Universe CRUD

### Backend
- [ ] Create `/api/universes` GET (list)
- [ ] Create `/api/universes` POST (create)
- [ ] Create `/api/universes/:id` GET (detail)
- [ ] Create `/api/universes/:id` PUT (update)
- [ ] Create `/api/universes/:id` DELETE (delete)

### Frontend
- [ ] Create `useUniverse` composable
- [ ] Create dashboard page (list universes)
- [ ] Create "New Universe" modal/page
- [ ] Create universe settings page
- [ ] Add delete confirmation modal
- [ ] Handle empty state (no universes)

---

## Phase 4: Timeline CRUD

### Backend
- [ ] Create `/api/universes/:id/timelines` GET (list)
- [ ] Create `/api/universes/:id/timelines` POST (create)
- [ ] Create `/api/timelines/:id` GET (detail with scenes)
- [ ] Create `/api/timelines/:id` PUT (update)
- [ ] Create `/api/timelines/:id` DELETE (delete)

### Frontend
- [ ] Create `useTimeline` composable
- [ ] Create timeline sidebar component
- [ ] Create "New Timeline" modal
- [ ] Add timeline color picker
- [ ] Mark canon timeline indicator
- [ ] Show branch origin for alternate timelines

---

## Phase 5: Scene CRUD

### Backend
- [ ] Create `/api/timelines/:id/scenes` GET (list)
- [ ] Create `/api/timelines/:id/scenes` POST (create)
- [ ] Create `/api/scenes/:id` GET (detail)
- [ ] Create `/api/scenes/:id` PUT (update)
- [ ] Create `/api/scenes/:id` DELETE (delete)
- [ ] Create `/api/scenes/:id/reorder` PUT (change order)

### Frontend
- [ ] Create `useScene` composable
- [ ] Create scene list view
- [ ] Create scene card component
- [ ] Create "New Scene" modal
- [ ] Implement drag-and-drop reordering

---

## Phase 6: Rich Text Editor

### Setup
- [ ] Install TipTap
- [ ] Configure TipTap extensions (bold, italic, etc.)
- [ ] Style editor with Duolingo theme

### SceneEditor Component
- [ ] Create SceneEditor.vue
- [ ] Add toolbar (formatting buttons)
- [ ] Add character dialogue styling
- [ ] Add inner monologue styling
- [ ] Add timestamp styling
- [ ] Implement auto-save (debounced)
- [ ] Show word count

### Metadata Panel
- [ ] Create MetadataPanel.vue
- [ ] Add title input
- [ ] Add date/time inputs
- [ ] Add location input
- [ ] Add mood selector
- [ ] Add POV selector
- [ ] Add character multi-select
- [ ] Add tag multi-select

---

## Phase 7: Graph Visualization

### Setup
- [ ] Install Vue Flow
- [ ] Configure Vue Flow with custom nodes
- [ ] Style graph with Duolingo theme

### Components
- [ ] Create TimelineGraph.vue (main container)
- [ ] Create SceneNode.vue (custom node)
- [ ] Create BranchLine.vue (custom edge)
- [ ] Add zoom controls
- [ ] Add pan functionality
- [ ] Add minimap (optional)

### Interactions
- [ ] Click node to select scene
- [ ] Double-click to open editor
- [ ] Highlight current timeline path
- [ ] Show branch points with special indicator
- [ ] Animate transitions between views

---

## Phase 8: Branching System

### Backend
- [ ] Create `/api/scenes/:id/branch` POST (create branch)
- [ ] Update scene with `isBranchPoint` flag
- [ ] Create new timeline from branch
- [ ] Copy subsequent scenes (optional)

### Frontend
- [ ] Create BranchPanel.vue
- [ ] Add "Mark as Branch Point" toggle
- [ ] Add "Create Alternate Timeline" button
- [ ] Add branch question input
- [ ] Show branch indicator on scene node
- [ ] Show branch connections in graph
- [ ] List all branches from a scene

---

## Phase 9: Characters

### Backend
- [ ] Create `/api/universes/:id/characters` GET
- [ ] Create `/api/universes/:id/characters` POST
- [ ] Create `/api/characters/:id` PUT
- [ ] Create `/api/characters/:id` DELETE

### Frontend
- [ ] Create characters management page
- [ ] Create CharacterCard component
- [ ] Create CharacterAvatar component
- [ ] Create "New Character" modal
- [ ] Add character color picker
- [ ] Add character type selector (INFJ, INFP, etc.)
- [ ] Add traits editor (tags)
- [ ] Link characters to scenes

---

## Phase 10: Tags

### Backend
- [ ] Create `/api/universes/:id/tags` GET
- [ ] Create `/api/universes/:id/tags` POST
- [ ] Create `/api/tags/:id` DELETE
- [ ] Auto-create tags when adding to scene

### Frontend
- [ ] Create TagChip component
- [ ] Create TagSelector component (multi-select)
- [ ] Create tags management page
- [ ] Add tag color picker
- [ ] Add tag category selector
- [ ] Filter scenes by tag in sidebar

---

## Phase 11: Search & Filter

### Backend
- [ ] Create `/api/search` GET endpoint
- [ ] Implement full-text search (MySQL FULLTEXT)
- [ ] Filter by tags
- [ ] Filter by characters
- [ ] Filter by mood
- [ ] Filter by timeline
- [ ] Sort by date/order

### Frontend
- [ ] Create `useSearch` composable
- [ ] Create search input component
- [ ] Create filter panel component
- [ ] Show search results
- [ ] Highlight search terms
- [ ] Navigate to scene from results

---

## Phase 12: Import (raw.md Parser)

### Parser Logic
- [ ] Create extractScenes.ts
- [ ] Detect scene start patterns
- [ ] Detect scene end patterns
- [ ] Filter out prompts/meta content
- [ ] Extract title from scene header
- [ ] Extract timestamps
- [ ] Create extractMetadata.ts
- [ ] Detect characters from dialogue
- [ ] Detect mood from keywords
- [ ] Detect location from keywords
- [ ] Detect potential branch points
- [ ] Generate tags from content

### Backend
- [ ] Create `/api/import/raw` POST endpoint
- [ ] Parse uploaded file
- [ ] Create universe from parsed data
- [ ] Create timeline (canon)
- [ ] Create scenes with metadata
- [ ] Create characters (INFJ, INFP)
- [ ] Create tags

### Frontend
- [ ] Create import page
- [ ] Add file upload component
- [ ] Show parsing preview
- [ ] Allow editing before import
- [ ] Show import progress
- [ ] Handle import errors

---

## Phase 13: Public/Private & Sharing

### Backend
- [ ] Add `isPublic` to universe
- [ ] Add `allowFork` to universe
- [ ] Create `/api/explore` GET (public universes)
- [ ] Create `/api/universes/:id/fork` POST
- [ ] Track forked_from relationship

### Frontend
- [ ] Add visibility toggle in settings
- [ ] Add fork permission toggle
- [ ] Create explore page (public universes)
- [ ] Create "Fork" button
- [ ] Show fork count
- [ ] Show "forked from" attribution

---

## Phase 14: Polish & UX

### Animations
- [ ] Add staggered list animations
- [ ] Add page transitions
- [ ] Add modal open/close animations
- [ ] Add toast slide-in animation
- [ ] Add button press animation (3D depth)
- [ ] Add graph node hover animation

### Mobile Responsive
- [ ] Make sidebar collapsible
- [ ] Create mobile navigation
- [ ] Make graph zoomable on touch
- [ ] Make editor mobile-friendly
- [ ] Test on various screen sizes

### Quality of Life
- [ ] Add keyboard shortcuts
- [ ] Add loading states (skeleton)
- [ ] Add error boundaries
- [ ] Add empty states with illustrations
- [ ] Add tooltips for icons
- [ ] Add confirmation for destructive actions

---

## Phase 15: Export & Backup

### Export Formats
- [ ] Export as Markdown
- [ ] Export as PDF
- [ ] Export as JSON (full backup)
- [ ] Export single timeline
- [ ] Export single scene

### Frontend
- [ ] Add export dropdown menu
- [ ] Show export progress
- [ ] Download generated file

---

## Phase 16: OAuth (Future)

- [ ] Setup Google OAuth provider
- [ ] Setup GitHub OAuth provider
- [ ] Setup Discord OAuth provider (optional)
- [ ] Link OAuth to existing account
- [ ] Handle OAuth errors

---

## Bug Fixes & Issues

> Track bugs as they're discovered

- [ ] ...

---

## Notes

### MySQL Connection
```
Host: localhost
Port: 3306
Database: storybranch
```

### Color Reference
```
Primary Blue: #1CB0F6
Primary Dark: #1899D6
Primary Light: #DBEAFE
```

### Fonts
```
Family: Nunito
Weights: 500, 600, 700, 800
```

---

## Progress Summary

| Phase | Status | Progress |
|-------|--------|----------|
| 1. Foundation | 🔲 Not Started | 0/11 |
| 2. Authentication | 🔲 Not Started | 0/14 |
| 3. Universe CRUD | 🔲 Not Started | 0/11 |
| 4. Timeline CRUD | 🔲 Not Started | 0/11 |
| 5. Scene CRUD | 🔲 Not Started | 0/10 |
| 6. Rich Text Editor | 🔲 Not Started | 0/16 |
| 7. Graph Visualization | 🔲 Not Started | 0/12 |
| 8. Branching System | 🔲 Not Started | 0/12 |
| 9. Characters | 🔲 Not Started | 0/12 |
| 10. Tags | 🔲 Not Started | 0/10 |
| 11. Search & Filter | 🔲 Not Started | 0/14 |
| 12. Import (Parser) | 🔲 Not Started | 0/19 |
| 13. Public/Private | 🔲 Not Started | 0/10 |
| 14. Polish & UX | 🔲 Not Started | 0/15 |
| 15. Export | 🔲 Not Started | 0/7 |
| 16. OAuth | 🔲 Not Started | 0/5 |

**Total: 0/179 tasks completed**

---

Last updated: 2026-02-06
