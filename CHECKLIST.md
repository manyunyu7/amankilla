# StoryBranch - Development Checklist

> Track progress for building the branching narrative platform

---

## Phase 1: Foundation

### Project Setup
- [x] Create Laravel 12 project
- [x] Install Laravel Breeze with Vue + Inertia
- [x] Configure Tailwind CSS 4.0
- [x] Install additional packages (TipTap, Vue Flow, Pinia, VueUse)
- [x] Setup Nunito font (Google Fonts)
- [x] Create folder structure
- [x] Setup environment variables (.env)
- [x] Configure Vite build

### Database
- [x] Create MySQL database `storybranch`
- [x] Configure database connection
- [x] Create migrations (User, Universe, Timeline, Scene, etc.)
- [x] Create Eloquent models with relationships
- [x] Run initial migration
- [x] Test database connection

### Design System (Duolingo Blue)
- [x] Define color palette (colors.js)
- [x] Create DButton component (3D effect)
- [x] Create DCard component (3D effect)
- [x] Create DInput component
- [x] Create DBadge component
- [x] Create DToggle component
- [x] Create DToast component
- [x] Create DModal component
- [x] Create DBottomSheet component
- [ ] Test all components in a sandbox page

---

## Phase 2: Authentication

### Backend (Laravel Breeze)
- [x] Install Breeze with Vue stack
- [x] Customize User model (add username, avatar_url)
- [x] Create username migration
- [x] Update RegisteredUserController for username
- [x] Test registration flow
- [x] Test login flow
- [x] Test logout flow

### Frontend
- [x] Customize login page with Duolingo style
- [x] Customize register page with Duolingo style
- [x] Create AppLayout.vue
- [x] Create GuestLayout.vue
- [x] Add user dropdown in header
- [x] Handle auth errors gracefully

---

## Phase 3: Universe CRUD

### Backend
- [x] Create Universe model
- [x] Create UniverseController
- [x] Create StoreUniverseRequest validation
- [x] Create UpdateUniverseRequest validation
- [x] Implement index (list user's universes)
- [x] Implement store (create universe)
- [x] Implement show (universe detail)
- [x] Implement update (edit universe)
- [x] Implement destroy (delete universe)

### Frontend
- [x] Create Dashboard.vue (list universes)
- [x] Create universe store (Pinia)
- [x] Create "New Universe" modal
- [x] Create Universe/Settings.vue
- [x] Add delete confirmation modal
- [x] Handle empty state (no universes)

---

## Phase 4: Timeline CRUD

### Backend
- [x] Create Timeline model
- [x] Create TimelineController
- [x] Create StoreTimelineRequest validation
- [x] Implement index (list timelines in universe)
- [x] Implement store (create timeline)
- [x] Implement show (timeline with scenes)
- [x] Implement update (edit timeline)
- [x] Implement destroy (delete timeline)

### Frontend
- [x] Create timeline store (Pinia)
- [x] Create timeline sidebar component
- [x] Create "New Timeline" modal
- [x] Add timeline color picker
- [x] Mark canon timeline indicator
- [x] Show branch origin for alternate timelines

---

## Phase 5: Scene CRUD

### Backend
- [x] Create Scene model
- [x] Create SceneController
- [x] Create StoreSceneRequest validation
- [x] Implement index (list scenes in timeline)
- [x] Implement store (create scene)
- [x] Implement show (scene detail)
- [x] Implement update (edit scene)
- [x] Implement destroy (delete scene)
- [x] Implement reorder (change scene order)

### Frontend
- [x] Create scene store (Pinia)
- [x] Create scene list view
- [x] Create scene card component
- [x] Create "New Scene" modal
- [x] Implement drag-and-drop reordering

---

## Phase 6: Rich Text Editor

### Setup
- [x] Install TipTap packages
- [x] Configure TipTap extensions (bold, italic, etc.)
- [x] Style editor with Duolingo theme

### SceneEditor Component
- [x] Create SceneEditor.vue
- [x] Add toolbar (formatting buttons)
- [ ] Add character dialogue styling
- [ ] Add inner monologue styling
- [ ] Add timestamp styling
- [x] Implement auto-save (debounced)
- [x] Show word count

### MetadataPanel Component
- [x] Create MetadataPanel.vue
- [x] Add title input
- [x] Add date/time inputs
- [x] Add location input
- [x] Add mood selector
- [x] Add POV selector
- [x] Add character multi-select
- [x] Add tag multi-select

---

## Phase 7: Graph Visualization

### Setup
- [x] Install Vue Flow
- [x] Configure Vue Flow with custom nodes
- [x] Style graph with Duolingo theme

### Components
- [x] Create TimelineGraph.vue (main container)
- [x] Create SceneNode.vue (custom node)
- [x] Create BranchLine.vue (custom edge) - using smoothstep edges
- [x] Add zoom controls
- [x] Add pan functionality
- [x] Add minimap (optional)

### Interactions
- [x] Click node to select scene
- [x] Double-click to open editor
- [ ] Highlight current timeline path
- [x] Show branch points with special indicator
- [ ] Animate transitions between views

---

## Phase 8: Branching System

### Backend
- [x] Add branch method to SceneController
- [x] Update scene with `is_branch_point` flag
- [x] Create new timeline from branch
- [x] Copy subsequent scenes (optional)

### Frontend
- [x] Create BranchPanel.vue
- [x] Add "Mark as Branch Point" toggle
- [x] Add "Create Alternate Timeline" button
- [x] Add branch question input
- [x] Show branch indicator on scene node
- [x] Show branch connections in graph
- [x] List all branches from a scene

---

## Phase 9: Characters

### Backend
- [x] Create Character model
- [x] Create CharacterController
- [x] Implement CRUD operations
- [x] Create scene_character pivot table
- [x] Add character relationships to Scene model

### Frontend
- [x] Create Universe/Characters.vue page
- [x] Create CharacterCard component
- [x] Create CharacterAvatar component
- [x] Create "New Character" modal
- [x] Add character color picker
- [x] Add character type selector (INFJ, INFP, etc.)
- [x] Add traits editor (tags)
- [x] Link characters to scenes in MetadataPanel

---

## Phase 10: Tags

### Backend
- [x] Create Tag model
- [x] Create TagController
- [x] Implement CRUD operations
- [x] Create scene_tag pivot table
- [x] Auto-create tags when adding to scene

### Frontend
- [x] Create TagChip component
- [x] Create TagSelector component (multi-select)
- [x] Create tags management section
- [x] Add tag color picker
- [x] Add tag category selector
- [x] Filter scenes by tag in sidebar

---

## Phase 11: Search & Filter

### Backend
- [x] Create SearchController
- [x] Implement full-text search (MySQL FULLTEXT)
- [x] Filter by tags
- [x] Filter by characters
- [x] Filter by mood
- [x] Filter by timeline
- [x] Sort by date/order

### Frontend
- [x] Create search store (Pinia)
- [x] Create search input component
- [x] Create filter panel component
- [x] Show search results
- [x] Highlight search terms
- [x] Navigate to scene from results

---

## Phase 12: Import (raw.md Parser)

### Parser Service
- [x] Create RawParser service class
- [x] Detect scene start patterns
- [x] Detect scene end patterns
- [x] Filter out prompts/meta content
- [x] Extract title from scene header
- [x] Extract timestamps
- [x] Detect characters from dialogue
- [x] Detect mood from keywords
- [x] Detect location from keywords
- [x] Detect potential branch points
- [x] Generate tags from content

### Backend
- [x] Create ImportController
- [x] Handle file upload
- [x] Parse uploaded file with RawParser
- [x] Create universe from parsed data
- [x] Create timeline (canon)
- [x] Create scenes with metadata
- [x] Create characters (INFJ, INFP)
- [x] Create tags

### Frontend
- [x] Create Universe/Import.vue page
- [x] Add file upload component
- [x] Show parsing preview
- [x] Allow editing before import
- [x] Show import progress
- [x] Handle import errors

---

## Phase 13: Public/Private & Sharing

### Backend
- [x] Add `is_public` to universe
- [x] Add `allow_fork` to universe
- [x] Create ExploreController (public universes)
- [x] Implement fork functionality
- [x] Track forked_from relationship

### Frontend
- [x] Add visibility toggle in settings
- [x] Add fork permission toggle
- [x] Create Explore.vue page (public universes)
- [x] Create "Fork" button
- [x] Show fork count
- [x] Show "forked from" attribution

---

## Phase 14: Polish & UX

### Animations
- [x] Add staggered list animations
- [ ] Add page transitions
- [x] Add modal open/close animations
- [x] Add toast slide-in animation
- [x] Add button press animation (3D depth)
- [x] Add graph node hover animation

### Mobile Responsive
- [x] Make sidebar collapsible
- [ ] Create mobile navigation
- [ ] Make graph zoomable on touch
- [ ] Make editor mobile-friendly
- [ ] Test on various screen sizes

### Quality of Life
- [x] Add keyboard shortcuts
- [x] Add loading states (skeleton)
- [ ] Add error boundaries
- [ ] Add empty states with illustrations
- [x] Add tooltips for icons
- [ ] Add confirmation for destructive actions

---

## Phase 15: Export & Backup

### Export Formats
- [x] Export as Markdown
- [ ] Export as PDF (future)
- [x] Export as JSON (full backup)
- [x] Export single timeline
- [x] Export single scene

### Frontend
- [x] Add export dropdown menu
- [x] Show export progress
- [x] Download generated file

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

### Development Commands
```bash
# Start all services
composer dev

# Run tests
composer test

# Fresh install
composer setup
```

---

## Progress Summary

| Phase | Status | Progress |
|-------|--------|----------|
| 1. Foundation | Complete | 24/25 |
| 2. Authentication | Complete | 13/13 |
| 3. Universe CRUD | Complete | 15/15 |
| 4. Timeline CRUD | Complete | 14/14 |
| 5. Scene CRUD | Complete | 14/14 |
| 6. Rich Text Editor | In Progress | 15/16 |
| 7. Graph Visualization | In Progress | 10/12 |
| 8. Branching System | Complete | 11/11 |
| 9. Characters | Complete | 13/13 |
| 10. Tags | Complete | 11/11 |
| 11. Search & Filter | Complete | 13/13 |
| 12. Import (Parser) | Complete | 18/18 |
| 13. Public/Private | Complete | 11/11 |
| 14. Polish & UX | In Progress | 9/15 |
| 15. Export | Complete | 7/8 |
| 16. OAuth | Not Started | 0/5 |

**Total: 198/203 tasks completed**

---

Last updated: 2026-02-06
