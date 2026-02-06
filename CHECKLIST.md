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
- [ ] Customize login page with Duolingo style
- [ ] Customize register page with Duolingo style
- [x] Create AppLayout.vue
- [x] Create GuestLayout.vue
- [ ] Add user dropdown in header
- [ ] Handle auth errors gracefully

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
- [ ] Create timeline store (Pinia)
- [x] Create timeline sidebar component
- [x] Create "New Timeline" modal
- [x] Add timeline color picker
- [x] Mark canon timeline indicator
- [ ] Show branch origin for alternate timelines

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
- [ ] Create scene store (Pinia)
- [x] Create scene list view
- [x] Create scene card component
- [x] Create "New Scene" modal
- [ ] Implement drag-and-drop reordering

---

## Phase 6: Rich Text Editor

### Setup
- [ ] Install TipTap packages
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

### MetadataPanel Component
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
- [ ] Add branch method to SceneController
- [ ] Update scene with `is_branch_point` flag
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
- [ ] Create Character model
- [ ] Create CharacterController
- [ ] Implement CRUD operations
- [ ] Create scene_character pivot table
- [ ] Add character relationships to Scene model

### Frontend
- [ ] Create Universe/Characters.vue page
- [ ] Create CharacterCard component
- [ ] Create CharacterAvatar component
- [ ] Create "New Character" modal
- [ ] Add character color picker
- [ ] Add character type selector (INFJ, INFP, etc.)
- [ ] Add traits editor (tags)
- [ ] Link characters to scenes in MetadataPanel

---

## Phase 10: Tags

### Backend
- [ ] Create Tag model
- [ ] Create TagController
- [ ] Implement CRUD operations
- [ ] Create scene_tag pivot table
- [ ] Auto-create tags when adding to scene

### Frontend
- [ ] Create TagChip component
- [ ] Create TagSelector component (multi-select)
- [ ] Create tags management section
- [ ] Add tag color picker
- [ ] Add tag category selector
- [ ] Filter scenes by tag in sidebar

---

## Phase 11: Search & Filter

### Backend
- [ ] Create SearchController
- [ ] Implement full-text search (MySQL FULLTEXT)
- [ ] Filter by tags
- [ ] Filter by characters
- [ ] Filter by mood
- [ ] Filter by timeline
- [ ] Sort by date/order

### Frontend
- [ ] Create search store (Pinia)
- [ ] Create search input component
- [ ] Create filter panel component
- [ ] Show search results
- [ ] Highlight search terms
- [ ] Navigate to scene from results

---

## Phase 12: Import (raw.md Parser)

### Parser Service
- [ ] Create RawParser service class
- [ ] Detect scene start patterns
- [ ] Detect scene end patterns
- [ ] Filter out prompts/meta content
- [ ] Extract title from scene header
- [ ] Extract timestamps
- [ ] Detect characters from dialogue
- [ ] Detect mood from keywords
- [ ] Detect location from keywords
- [ ] Detect potential branch points
- [ ] Generate tags from content

### Backend
- [ ] Create ImportController
- [ ] Handle file upload
- [ ] Parse uploaded file with RawParser
- [ ] Create universe from parsed data
- [ ] Create timeline (canon)
- [ ] Create scenes with metadata
- [ ] Create characters (INFJ, INFP)
- [ ] Create tags

### Frontend
- [ ] Create Universe/Import.vue page
- [ ] Add file upload component
- [ ] Show parsing preview
- [ ] Allow editing before import
- [ ] Show import progress
- [ ] Handle import errors

---

## Phase 13: Public/Private & Sharing

### Backend
- [ ] Add `is_public` to universe
- [ ] Add `allow_fork` to universe
- [ ] Create ExploreController (public universes)
- [ ] Implement fork functionality
- [ ] Track forked_from relationship

### Frontend
- [ ] Add visibility toggle in settings
- [ ] Add fork permission toggle
- [ ] Create Explore.vue page (public universes)
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
| 2. Authentication | In Progress | 9/13 |
| 3. Universe CRUD | Complete | 15/15 |
| 4. Timeline CRUD | In Progress | 12/14 |
| 5. Scene CRUD | In Progress | 12/14 |
| 6. Rich Text Editor | Not Started | 0/16 |
| 7. Graph Visualization | Not Started | 0/12 |
| 8. Branching System | Not Started | 0/11 |
| 9. Characters | Not Started | 0/13 |
| 10. Tags | Not Started | 0/11 |
| 11. Search & Filter | Not Started | 0/13 |
| 12. Import (Parser) | Not Started | 0/18 |
| 13. Public/Private | Not Started | 0/11 |
| 14. Polish & UX | Not Started | 0/15 |
| 15. Export | Not Started | 0/7 |
| 16. OAuth | Not Started | 0/5 |

**Total: 72/194 tasks completed**

---

Last updated: 2026-02-06
