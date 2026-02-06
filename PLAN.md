# StoryBranch - Project Plan

> Multi-user branching narrative platform with Duolingo-style UI (Blue theme)

---

## 1. Overview

### What is this?
A web app where users can:
- Create story universes with multiple timelines
- Visualize branching narratives (like the life paths diagram)
- Edit scenes with rich text
- Create "what if" alternate branches from any decision point
- Search/filter by tags, characters, mood, etc.

### Tech Stack
| Layer | Technology |
|-------|------------|
| Frontend | Nuxt 3 (Vue.js) |
| Styling | Tailwind CSS + Custom Duolingo components |
| Database | MySQL 9.2.0 |
| ORM | Prisma |
| Auth | Email/Password (OAuth later) |
| Graph Viz | Vue Flow |
| Rich Editor | TipTap |
| Font | Nunito (Google Fonts) |

---

## 2. Database Schema

```prisma
// prisma/schema.prisma

generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "mysql"
  url      = env("DATABASE_URL")
}

// ============== AUTH ==============

model User {
  id            String    @id @default(cuid())
  username      String    @unique
  email         String    @unique
  passwordHash  String
  avatarUrl     String?
  createdAt     DateTime  @default(now())
  updatedAt     DateTime  @updatedAt

  universes     Universe[]
  sessions      Session[]
}

model Session {
  id        String   @id @default(cuid())
  userId    String
  token     String   @unique
  expiresAt DateTime
  createdAt DateTime @default(now())

  user      User     @relation(fields: [userId], references: [id], onDelete: Cascade)

  @@index([userId])
}

// ============== STORY STRUCTURE ==============

model Universe {
  id          String    @id @default(cuid())
  userId      String
  name        String
  description String?   @db.Text
  coverImage  String?
  isPublic    Boolean   @default(false)
  allowFork   Boolean   @default(false)
  createdAt   DateTime  @default(now())
  updatedAt   DateTime  @updatedAt

  user        User       @relation(fields: [userId], references: [id], onDelete: Cascade)
  characters  Character[]
  timelines   Timeline[]
  tags        Tag[]

  @@index([userId])
  @@index([isPublic])
}

model Character {
  id          String    @id @default(cuid())
  universeId  String
  name        String
  nickname    String?
  type        String?   // e.g., "INFJ", "INFP", "protagonist", "antagonist"
  description String?   @db.Text
  traits      Json?     // ["caring", "analytical", "overthinking"]
  avatarUrl   String?
  color       String?   // For UI identification
  createdAt   DateTime  @default(now())
  updatedAt   DateTime  @updatedAt

  universe    Universe  @relation(fields: [universeId], references: [id], onDelete: Cascade)
  scenes      SceneCharacter[]

  @@index([universeId])
}

model Timeline {
  id              String    @id @default(cuid())
  universeId      String
  name            String    // "Canon", "What if breakup", etc.
  description     String?   @db.Text
  isCanon         Boolean   @default(false)
  color           String?   // For graph visualization
  branchFromId    String?   // Scene ID where this timeline branches from
  createdAt       DateTime  @default(now())
  updatedAt       DateTime  @updatedAt

  universe        Universe  @relation(fields: [universeId], references: [id], onDelete: Cascade)
  branchFrom      Scene?    @relation("BranchOrigin", fields: [branchFromId], references: [id])
  scenes          Scene[]

  @@index([universeId])
}

model Scene {
  id          String    @id @default(cuid())
  timelineId  String
  title       String
  content     String    @db.LongText  // Rich text (HTML/JSON from TipTap)
  summary     String?   @db.Text      // Short description for navigation
  order       Int       // Position in timeline

  // Metadata
  date        String?   // In-story date (e.g., "Feb 2", "Malam sebelumnya")
  time        String?   // In-story time (e.g., "21.47", "Pagi")
  location    String?   // e.g., "Kos Bandung", "Kereta", "Stasiun Garut"
  mood        String?   // "warm", "tense", "playful", "sad"
  pov         String?   // Point of view: "INFJ", "INFP", "dual", "narrator"
  wordCount   Int       @default(0)

  // Branch info
  isBranchPoint   Boolean   @default(false)
  branchQuestion  String?   // "What if INFJ revealed her planning?"

  createdAt   DateTime  @default(now())
  updatedAt   DateTime  @updatedAt

  timeline    Timeline  @relation(fields: [timelineId], references: [id], onDelete: Cascade)
  characters  SceneCharacter[]
  tags        SceneTag[]
  branchesTo  Timeline[] @relation("BranchOrigin")

  @@index([timelineId])
  @@index([order])
}

model SceneCharacter {
  sceneId     String
  characterId String

  scene       Scene     @relation(fields: [sceneId], references: [id], onDelete: Cascade)
  character   Character @relation(fields: [characterId], references: [id], onDelete: Cascade)

  @@id([sceneId, characterId])
}

// ============== TAGGING ==============

model Tag {
  id          String    @id @default(cuid())
  universeId  String
  name        String    // "cute", "conflict", "milestone", "first-kiss"
  color       String?   // Hex color for UI
  category    String?   // "emotion", "event", "theme"

  universe    Universe  @relation(fields: [universeId], references: [id], onDelete: Cascade)
  scenes      SceneTag[]

  @@unique([universeId, name])
  @@index([universeId])
}

model SceneTag {
  sceneId String
  tagId   String

  scene   Scene @relation(fields: [sceneId], references: [id], onDelete: Cascade)
  tag     Tag   @relation(fields: [tagId], references: [id], onDelete: Cascade)

  @@id([sceneId, tagId])
}
```

---

## 3. Folder Structure

```
/amankila/
├── .nuxt/
├── node_modules/
├── prisma/
│   ├── schema.prisma
│   ├── migrations/
│   └── seed.ts              # Import raw.md data
│
├── server/
│   ├── api/
│   │   ├── auth/
│   │   │   ├── login.post.ts
│   │   │   ├── register.post.ts
│   │   │   ├── logout.post.ts
│   │   │   └── me.get.ts
│   │   │
│   │   ├── universes/
│   │   │   ├── index.get.ts        # List user's universes
│   │   │   ├── index.post.ts       # Create universe
│   │   │   ├── [id].get.ts         # Get universe details
│   │   │   ├── [id].put.ts         # Update universe
│   │   │   ├── [id].delete.ts      # Delete universe
│   │   │   └── [id]/
│   │   │       ├── characters/
│   │   │       ├── timelines/
│   │   │       └── tags/
│   │   │
│   │   ├── timelines/
│   │   │   ├── [id].get.ts
│   │   │   ├── [id].put.ts
│   │   │   ├── [id].delete.ts
│   │   │   └── [id]/scenes/
│   │   │
│   │   ├── scenes/
│   │   │   ├── [id].get.ts
│   │   │   ├── [id].put.ts
│   │   │   ├── [id].delete.ts
│   │   │   └── branch.post.ts      # Create branch from scene
│   │   │
│   │   ├── import/
│   │   │   └── raw.post.ts         # Import from raw.md format
│   │   │
│   │   └── search/
│   │       └── index.get.ts        # Full-text search
│   │
│   ├── middleware/
│   │   └── auth.ts
│   │
│   └── utils/
│       ├── db.ts                   # Prisma client
│       ├── parser.ts               # raw.md parser
│       └── auth.ts                 # Password hashing, tokens
│
├── components/
│   ├── ui/                         # Duolingo-style components
│   │   ├── DButton.vue
│   │   ├── DCard.vue
│   │   ├── DInput.vue
│   │   ├── DBadge.vue
│   │   ├── DToggle.vue
│   │   ├── DToast.vue
│   │   ├── DModal.vue
│   │   └── DBottomSheet.vue
│   │
│   ├── graph/
│   │   ├── TimelineGraph.vue       # Main branching visualization
│   │   ├── SceneNode.vue           # Single node in graph
│   │   └── BranchLine.vue          # Connection lines
│   │
│   ├── editor/
│   │   ├── SceneEditor.vue         # TipTap rich text editor
│   │   ├── MetadataPanel.vue       # Tags, mood, characters
│   │   └── BranchPanel.vue         # Create/manage branches
│   │
│   ├── layout/
│   │   ├── Sidebar.vue
│   │   ├── Header.vue
│   │   └── MobileNav.vue
│   │
│   └── common/
│       ├── CharacterAvatar.vue
│       ├── TagChip.vue
│       └── TimelineSelector.vue
│
├── composables/
│   ├── useAuth.ts
│   ├── useUniverse.ts
│   ├── useTimeline.ts
│   ├── useScene.ts
│   ├── useSearch.ts
│   └── useToast.ts
│
├── pages/
│   ├── index.vue                   # Landing / Dashboard
│   ├── login.vue
│   ├── register.vue
│   │
│   ├── u/
│   │   └── [universe]/
│   │       ├── index.vue           # Universe overview (graph view)
│   │       ├── timeline/
│   │       │   └── [timeline].vue  # Timeline detail
│   │       ├── scene/
│   │       │   └── [scene].vue     # Scene editor
│   │       ├── characters.vue
│   │       ├── settings.vue
│   │       └── import.vue          # Import from file
│   │
│   └── explore.vue                 # Public universes
│
├── assets/
│   └── css/
│       ├── main.css
│       └── duolingo.css            # Custom Duolingo styles
│
├── public/
│   └── images/
│
├── utils/
│   ├── parser/
│   │   ├── index.ts
│   │   ├── extractScenes.ts        # Parse raw.md → scenes
│   │   ├── detectBranchPoints.ts
│   │   └── extractMetadata.ts
│   │
│   └── constants/
│       ├── colors.ts               # Duolingo blue palette
│       └── moods.ts
│
├── types/
│   └── index.ts                    # TypeScript types
│
├── nuxt.config.ts
├── tailwind.config.ts
├── package.json
├── tsconfig.json
├── .env
├── .env.example
├── PLAN.md                         # This file
└── raw.md                          # Your original data
```

---

## 4. Color Palette (Duolingo Blue Theme)

```ts
// utils/constants/colors.ts

export const colors = {
  // Primary (Blue instead of Green)
  primary: '#1CB0F6',
  primaryDark: '#1899D6',      // 3D shadow
  primaryLight: '#DBEAFE',     // Light background

  // Secondary (keeping some accent)
  secondary: '#3B82F6',
  secondaryDark: '#2563EB',

  // Success (Green - for completed, etc.)
  success: '#58CC02',
  successDark: '#46A302',
  successLight: '#D7FFB8',

  // Warning
  warning: '#F59E0B',
  warningDark: '#D97706',

  // Error
  error: '#EF4444',
  errorDark: '#DC2626',

  // Neutral
  white: '#FFFFFF',
  bgLight: '#F8F9FA',
  bgSheet: '#F5F5F5',
  bgLightGray: '#F3F4F6',

  // Borders
  borderGray: '#E5E5E5',
  borderDark: '#DEE2E6',

  // Text
  textPrimary: '#1F2937',
  textSecondary: '#6B7280',
  textHint: '#9CA3AF',
}
```

---

## 5. Key UI Components

### 5.1 DButton (3D Effect)

```vue
<!-- components/ui/DButton.vue -->
<template>
  <button
    :class="[
      'relative transition-all duration-100',
      'active:translate-y-[2px]',
      sizeClasses,
    ]"
    :disabled="disabled"
  >
    <!-- Shadow layer -->
    <span
      :class="[
        'absolute inset-0 rounded-[22px]',
        shadowColorClass,
      ]"
      :style="{ transform: 'translateY(4px)' }"
    />

    <!-- Main button -->
    <span
      :class="[
        'relative flex items-center justify-center gap-2',
        'rounded-[22px] font-nunito font-bold',
        'transition-transform duration-100',
        mainColorClass,
        'border-2',
        borderColorClass,
      ]"
    >
      <slot />
    </span>
  </button>
</template>
```

### 5.2 DCard (3D Card)

```vue
<!-- components/ui/DCard.vue -->
<template>
  <div class="relative">
    <!-- Shadow -->
    <div
      class="absolute inset-0 bg-gray-200 rounded-2xl"
      style="transform: translateY(4px)"
    />

    <!-- Card -->
    <div
      class="relative bg-white rounded-2xl border-2 border-gray-200 p-4"
    >
      <slot />
    </div>
  </div>
</template>
```

### 5.3 Scene Node (Graph)

```vue
<!-- components/graph/SceneNode.vue -->
<template>
  <div
    :class="[
      'relative cursor-pointer transition-all',
      'hover:scale-105',
      isSelected && 'ring-2 ring-primary ring-offset-2'
    ]"
    @click="$emit('select', scene.id)"
  >
    <!-- Shadow -->
    <div
      class="absolute inset-0 rounded-xl"
      :class="isBranchPoint ? 'bg-primary-dark' : 'bg-gray-200'"
      style="transform: translateY(4px)"
    />

    <!-- Node -->
    <div
      :class="[
        'relative rounded-xl border-2 p-3 min-w-[120px]',
        isBranchPoint
          ? 'bg-primary-light border-primary'
          : 'bg-white border-gray-200'
      ]"
    >
      <div class="font-nunito font-bold text-sm truncate">
        {{ scene.title }}
      </div>
      <div class="text-xs text-gray-500 mt-1">
        {{ scene.date }} {{ scene.time }}
      </div>

      <!-- Branch indicator -->
      <div
        v-if="isBranchPoint"
        class="absolute -top-2 -right-2 w-6 h-6 bg-primary rounded-full flex items-center justify-center"
      >
        <Icon name="git-branch" class="w-3 h-3 text-white" />
      </div>
    </div>
  </div>
</template>
```

---

## 6. Parser Logic (raw.md → Database)

The raw.md contains mixed content:
- **User prompts** (questions to AI)
- **AI explanations** (meta-commentary about INFJ/INFP)
- **Actual story content** (scenes, dialogue, inner monologue)

### Detection Patterns

```ts
// utils/parser/extractScenes.ts

interface RawScene {
  title: string
  content: string
  date?: string
  time?: string
  lineStart: number
  lineEnd: number
}

// Patterns that indicate START of a story scene
const SCENE_START_PATTERNS = [
  /^🚂.*Story$/,                           // 🚂 Bandung → Garut: An INFJ × INFP Story
  /^💼\s*".+"/,                            // 💼 "Aku Mau Resign..."
  /^💔\s*".+"/,                            // 💔 "Kamu Kok Jarang..."
  /^##\s*Scene:/,                          // ## Scene: ...
  /^MALAM SEBELUMNYA$/,                    // Section headers
  /^PAGI\s*(—|HARI)?/,                     // PAGI — D-DAY
  /^\d{2}\.\d{2}\s*—/,                     // 21.47 — INFJ di kos
]

// Patterns that indicate this is NOT story (skip)
const SKIP_PATTERNS = [
  /^Bagaimana tipikal/,                    // User prompts
  /^Coba skenarionya/,
  /^Gimana infj/,
  /^Ini seru banget/,                      // AI meta-commentary
  /^Kalau ngebayangin/,
  /^Claude's response was interrupted/,
  /^Feb \d+$/,                             // Date markers (standalone)
]

// Patterns that indicate END of a story scene
const SCENE_END_PATTERNS = [
  /^fin\.\s*✨?$/,                         // fin. ✨
  /^---+$/,                                // Horizontal rule
]

export function extractScenes(rawContent: string): RawScene[] {
  const lines = rawContent.split('\n')
  const scenes: RawScene[] = []

  let currentScene: RawScene | null = null
  let inStoryMode = false

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim()

    // Check if we should skip this line
    if (SKIP_PATTERNS.some(p => p.test(line))) {
      continue
    }

    // Check for scene start
    if (SCENE_START_PATTERNS.some(p => p.test(line))) {
      // Save previous scene
      if (currentScene) {
        currentScene.lineEnd = i - 1
        scenes.push(currentScene)
      }

      // Start new scene
      currentScene = {
        title: extractTitle(line),
        content: '',
        lineStart: i,
        lineEnd: i,
      }
      inStoryMode = true
      continue
    }

    // Check for scene end
    if (SCENE_END_PATTERNS.some(p => p.test(line))) {
      if (currentScene) {
        currentScene.lineEnd = i
        scenes.push(currentScene)
        currentScene = null
      }
      inStoryMode = false
      continue
    }

    // Accumulate content if in story mode
    if (inStoryMode && currentScene) {
      // Extract time markers
      const timeMatch = line.match(/^(\d{2}\.\d{2})\s*—?\s*(.*)/)
      if (timeMatch) {
        currentScene.time = timeMatch[1]
      }

      currentScene.content += line + '\n'
    }
  }

  return scenes
}

function extractTitle(line: string): string {
  // Remove emoji prefixes and clean up
  return line
    .replace(/^[🚂💼💔🎬]\s*/, '')
    .replace(/^#+\s*/, '')
    .trim()
}
```

### Metadata Extraction

```ts
// utils/parser/extractMetadata.ts

interface SceneMetadata {
  characters: string[]
  mood: string
  location?: string
  tags: string[]
  isBranchPoint: boolean
  branchQuestion?: string
}

export function extractMetadata(content: string): SceneMetadata {
  const characters: string[] = []
  const tags: string[] = []

  // Detect characters by dialogue patterns
  if (/INFJ:/.test(content)) characters.push('INFJ')
  if (/INFP:/.test(content)) characters.push('INFP')

  // Detect mood from content
  const mood = detectMood(content)

  // Detect location
  const location = detectLocation(content)

  // Detect if branch point (key decisions)
  const isBranchPoint = detectBranchPoint(content)

  // Extract tags from content themes
  if (/Inner monologue/.test(content)) tags.push('inner-monologue')
  if (/[Cc]hat|[Mm]essage/.test(content)) tags.push('chat')
  if (/[Kk]ereta|[Ss]tasiun/.test(content)) tags.push('train')
  if (/[Cc]ium|[Pp]eluk/.test(content)) tags.push('intimate')
  if (/[Mm]arah|[Kk]esel/.test(content)) tags.push('conflict')

  return {
    characters,
    mood,
    location,
    tags,
    isBranchPoint,
  }
}

function detectMood(content: string): string {
  // Simple keyword-based mood detection
  const moodKeywords = {
    warm: ['senyum', 'seneng', 'bahagia', 'sweet', 'hangat'],
    tense: ['deg-degan', 'nervous', 'tegang', 'panik'],
    playful: ['haha', 'hehe', 'lucu', 'nyebelin'],
    sad: ['sedih', 'nangis', 'mata merah', 'sesak'],
    romantic: ['sayang', 'cium', 'peluk', 'love'],
  }

  const contentLower = content.toLowerCase()

  for (const [mood, keywords] of Object.entries(moodKeywords)) {
    if (keywords.some(k => contentLower.includes(k))) {
      return mood
    }
  }

  return 'neutral'
}

function detectLocation(content: string): string | undefined {
  const locationPatterns = [
    { pattern: /di kos/i, location: 'Kos' },
    { pattern: /[Kk]ereta|KA /i, location: 'Kereta' },
    { pattern: /[Ss]tasiun/i, location: 'Stasiun' },
    { pattern: /[Cc]afe|[Kk]afe/i, location: 'Cafe' },
    { pattern: /[Gg]arut/i, location: 'Garut' },
    { pattern: /[Bb]andung/i, location: 'Bandung' },
    { pattern: /[Jj]akarta/i, location: 'Jakarta' },
  ]

  for (const { pattern, location } of locationPatterns) {
    if (pattern.test(content)) {
      return location
    }
  }

  return undefined
}

function detectBranchPoint(content: string): boolean {
  // Key decision indicators
  const branchIndicators = [
    /[Kk]alau.*gimana/,       // "Kalau X gimana"
    /[Mm]au.*atau/,           // "Mau X atau Y"
    /pilih/i,                 // "pilih"
    /keputusan/i,             // "keputusan"
    /gimana kalo/i,           // "gimana kalo"
  ]

  return branchIndicators.some(p => p.test(content))
}
```

---

## 7. Features Breakdown

### Phase 1: Core (MVP)
- [ ] Auth (register, login, logout)
- [ ] Create/edit universe
- [ ] Create/edit timeline
- [ ] Create/edit scenes (rich text)
- [ ] Basic graph visualization
- [ ] Import from raw.md

### Phase 2: Branch System
- [ ] Mark scene as branch point
- [ ] Create alternate timeline from branch
- [ ] Visual branch connections in graph
- [ ] Compare timelines side-by-side

### Phase 3: Organization
- [ ] Characters management
- [ ] Tags system
- [ ] Search (full-text + filters)
- [ ] Mood/location metadata

### Phase 4: Social
- [ ] Public/private universes
- [ ] Fork public universes
- [ ] OAuth (Google, GitHub)

### Phase 5: Polish
- [ ] Mobile responsive
- [ ] Animations (staggered lists, transitions)
- [ ] Export (PDF, Markdown)
- [ ] Keyboard shortcuts

---

## 8. API Endpoints

### Auth
```
POST   /api/auth/register     # Create account
POST   /api/auth/login        # Login
POST   /api/auth/logout       # Logout
GET    /api/auth/me           # Current user
```

### Universes
```
GET    /api/universes                    # List my universes
POST   /api/universes                    # Create universe
GET    /api/universes/:id                # Get universe
PUT    /api/universes/:id                # Update universe
DELETE /api/universes/:id                # Delete universe
```

### Timelines
```
GET    /api/universes/:id/timelines      # List timelines
POST   /api/universes/:id/timelines      # Create timeline
GET    /api/timelines/:id                # Get timeline with scenes
PUT    /api/timelines/:id                # Update timeline
DELETE /api/timelines/:id                # Delete timeline
```

### Scenes
```
GET    /api/timelines/:id/scenes         # List scenes in timeline
POST   /api/timelines/:id/scenes         # Create scene
GET    /api/scenes/:id                   # Get scene
PUT    /api/scenes/:id                   # Update scene
DELETE /api/scenes/:id                   # Delete scene
POST   /api/scenes/:id/branch            # Create branch from scene
```

### Characters
```
GET    /api/universes/:id/characters     # List characters
POST   /api/universes/:id/characters     # Create character
PUT    /api/characters/:id               # Update character
DELETE /api/characters/:id               # Delete character
```

### Tags
```
GET    /api/universes/:id/tags           # List tags
POST   /api/universes/:id/tags           # Create tag
DELETE /api/tags/:id                     # Delete tag
```

### Import & Search
```
POST   /api/import/raw                   # Import from raw.md
GET    /api/search?q=...&tags=...        # Search scenes
```

---

## 9. Environment Variables

```env
# .env.example

# Database
DATABASE_URL="mysql://user:password@localhost:3306/storybranch"

# Auth
JWT_SECRET="your-super-secret-jwt-key"
SESSION_EXPIRY_DAYS=30

# App
NUXT_PUBLIC_APP_NAME="StoryBranch"
NUXT_PUBLIC_APP_URL="http://localhost:3000"
```

---

## 10. Next Steps

1. **Setup project** - `npx nuxi init amankila-app`
2. **Install dependencies** - Prisma, TipTap, Vue Flow, etc.
3. **Setup database** - Create MySQL database, run migrations
4. **Build auth** - Register, login, session management
5. **Build CRUD** - Universe, timeline, scene
6. **Build UI components** - Duolingo-style design system
7. **Build graph** - Timeline visualization
8. **Build parser** - Import raw.md
9. **Polish** - Animations, mobile, search

---

## 11. Design Mockups

### Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│  🌿 StoryBranch                          [+ New Universe]   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Your Universes                                             │
│  ─────────────────                                          │
│                                                             │
│  ┌─────────────────┐  ┌─────────────────┐                  │
│  │ ┌─────────────┐ │  │ ┌─────────────┐ │                  │
│  │ │  INFJ-INFP  │ │  │ │   New...    │ │                  │
│  │ │   Story     │ │  │ │     +       │ │                  │
│  │ │             │ │  │ │             │ │                  │
│  │ │ 7 timelines │ │  │ │             │ │                  │
│  │ │ 156 scenes  │ │  │ │             │ │                  │
│  │ └─────────────┘ │  │ └─────────────┘ │                  │
│  └─────────────────┘  └─────────────────┘                  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Universe View (Graph)
```
┌─────────────────────────────────────────────────────────────┐
│  ← Back    INFJ-INFP Story                    [Settings]    │
├──────────┬──────────────────────────────────────────────────┤
│          │                                                  │
│ Timelines│         ┌───┐                                    │
│ ─────────│         │ 1 │ Garut Trip                        │
│ ● Canon  │         └─┬─┘                                    │
│ ○ Alt 1  │           │                                      │
│ ○ Alt 2  │     ┌─────┼─────┐                                │
│          │     │     │     │                                │
│ Tags     │   ┌─┴─┐ ┌─┴─┐ ┌─┴─┐                              │
│ ─────────│   │ 2 │ │2b │ │2c │  ← Branch point             │
│ #cute    │   └─┬─┘ └───┘ └───┘                              │
│ #conflict│     │                                            │
│ #train   │   ┌─┴─┐                                          │
│          │   │ 3 │ First Kiss                               │
│ Search   │   └─┬─┘                                          │
│ ─────────│     │                                            │
│ [______] │   ┌─┴─┐                                          │
│          │   │...│                                          │
│          │   └───┘                                          │
│          │                                                  │
├──────────┴──────────────────────────────────────────────────┤
│ Scene: Garut Trip - INFJ Planning                    [Edit] │
│ ┌──────────────────────────────────────────────────────────┐│
│ │ 21.47 — INFJ di kos, Bandung                             ││
│ │                                                          ││
│ │ Duduk di kasur, bantal dipangku. HP di tangan...         ││
│ │                                                          ││
│ │ Tags: [train] [planning] [cute]                          ││
│ │ Mood: warm    Characters: INFJ                           ││
│ └──────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

---

Ready to start building?
