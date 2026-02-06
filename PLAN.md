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
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue.js 3.5+ with Inertia.js |
| Styling | Tailwind CSS 4.0 |
| Database | MySQL 9.2.0 |
| Auth | Laravel Sanctum + Breeze |
| Build | Vite 7.0 |
| State | Pinia |
| Graph Viz | Vue Flow |
| Rich Editor | TipTap |
| Font | Nunito (Google Fonts) |
| Routing | Ziggy |

---

## 2. Database Schema

```php
// database/migrations - Laravel Migrations

// ============== AUTH ==============
// Users table (Laravel default + custom fields)
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->string('password');
    $table->string('avatar_url')->nullable();
    $table->rememberToken();
    $table->timestamps();
});

// ============== STORY STRUCTURE ==============

Schema::create('universes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('cover_image')->nullable();
    $table->boolean('is_public')->default(false);
    $table->boolean('allow_fork')->default(false);
    $table->timestamps();

    $table->index('is_public');
});

Schema::create('characters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('nickname')->nullable();
    $table->string('type')->nullable(); // e.g., "INFJ", "INFP"
    $table->text('description')->nullable();
    $table->json('traits')->nullable(); // ["caring", "analytical"]
    $table->string('avatar_url')->nullable();
    $table->string('color')->nullable(); // For UI identification
    $table->timestamps();
});

Schema::create('timelines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
    $table->string('name'); // "Canon", "What if breakup"
    $table->text('description')->nullable();
    $table->boolean('is_canon')->default(false);
    $table->string('color')->nullable(); // For graph visualization
    $table->foreignId('branch_from_id')->nullable()->constrained('scenes');
    $table->timestamps();
});

Schema::create('scenes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('timeline_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->longText('content'); // Rich text (HTML/JSON from TipTap)
    $table->text('summary')->nullable();
    $table->integer('order');

    // Metadata
    $table->string('date')->nullable(); // In-story date
    $table->string('time')->nullable(); // In-story time
    $table->string('location')->nullable();
    $table->string('mood')->nullable(); // warm, tense, playful, sad
    $table->string('pov')->nullable(); // Point of view
    $table->integer('word_count')->default(0);

    // Branch info
    $table->boolean('is_branch_point')->default(false);
    $table->string('branch_question')->nullable();
    $table->timestamps();

    $table->index('order');
});

Schema::create('scene_character', function (Blueprint $table) {
    $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
    $table->foreignId('character_id')->constrained()->cascadeOnDelete();
    $table->primary(['scene_id', 'character_id']);
});

// ============== TAGGING ==============

Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
    $table->string('name'); // "cute", "conflict", "milestone"
    $table->string('color')->nullable();
    $table->string('category')->nullable(); // emotion, event, theme
    $table->timestamps();

    $table->unique(['universe_id', 'name']);
});

Schema::create('scene_tag', function (Blueprint $table) {
    $table->foreignId('scene_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->primary(['scene_id', 'tag_id']);
});
```

---

## 3. Folder Structure

```
/amankila/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/           # Laravel Breeze controllers
│   │   │   ├── UniverseController.php
│   │   │   ├── TimelineController.php
│   │   │   ├── SceneController.php
│   │   │   ├── CharacterController.php
│   │   │   ├── TagController.php
│   │   │   ├── ImportController.php
│   │   │   └── SearchController.php
│   │   ├── Requests/           # Form validation
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Universe.php
│   │   ├── Character.php
│   │   ├── Timeline.php
│   │   ├── Scene.php
│   │   └── Tag.php
│   │
│   └── Services/
│       └── RawParser.php       # Parse raw.md
│
├── resources/
│   ├── js/
│   │   ├── app.js              # Vue + Inertia entry
│   │   ├── Components/
│   │   │   ├── ui/             # Duolingo-style components
│   │   │   │   ├── DButton.vue
│   │   │   │   ├── DCard.vue
│   │   │   │   ├── DInput.vue
│   │   │   │   ├── DBadge.vue
│   │   │   │   ├── DToggle.vue
│   │   │   │   ├── DToast.vue
│   │   │   │   ├── DModal.vue
│   │   │   │   └── DBottomSheet.vue
│   │   │   │
│   │   │   ├── graph/
│   │   │   │   ├── TimelineGraph.vue
│   │   │   │   ├── SceneNode.vue
│   │   │   │   └── BranchLine.vue
│   │   │   │
│   │   │   ├── editor/
│   │   │   │   ├── SceneEditor.vue
│   │   │   │   ├── MetadataPanel.vue
│   │   │   │   └── BranchPanel.vue
│   │   │   │
│   │   │   └── layout/
│   │   │       ├── Sidebar.vue
│   │   │       ├── Header.vue
│   │   │       └── MobileNav.vue
│   │   │
│   │   ├── Pages/
│   │   │   ├── Auth/           # Login, Register (Breeze)
│   │   │   ├── Dashboard.vue
│   │   │   ├── Explore.vue
│   │   │   └── Universe/
│   │   │       ├── Index.vue   # Universe overview (graph)
│   │   │       ├── Settings.vue
│   │   │       ├── Characters.vue
│   │   │       ├── Import.vue
│   │   │       └── Scene/
│   │   │           └── Edit.vue
│   │   │
│   │   ├── Layouts/
│   │   │   ├── AppLayout.vue
│   │   │   └── GuestLayout.vue
│   │   │
│   │   ├── stores/             # Pinia stores
│   │   │   ├── auth.js
│   │   │   ├── universe.js
│   │   │   └── toast.js
│   │   │
│   │   └── utils/
│   │       └── colors.js       # Duolingo blue palette
│   │
│   ├── css/
│   │   └── app.css             # Tailwind + custom styles
│   │
│   └── views/
│       └── app.blade.php       # Inertia root template
│
├── routes/
│   ├── web.php                 # Inertia routes
│   └── api.php                 # API routes
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── config/
├── public/
├── storage/
├── tests/
│
├── composer.json
├── package.json
├── vite.config.js
├── tailwind.config.js
├── .env
├── .env.example
├── PLAN.md
├── CHECKLIST.md
└── raw.md
```

---

## 4. Color Palette (Duolingo Blue Theme)

```js
// resources/js/utils/colors.js

export const colors = {
  // Primary (Blue instead of Green)
  primary: '#1CB0F6',
  primaryDark: '#1899D6',      // 3D shadow
  primaryLight: '#DBEAFE',     // Light background

  // Secondary
  secondary: '#3B82F6',
  secondaryDark: '#2563EB',

  // Success (Green)
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
<!-- resources/js/Components/ui/DButton.vue -->
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
<!-- resources/js/Components/ui/DCard.vue -->
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

---

## 6. Parser Logic (raw.md -> Database)

```php
// app/Services/RawParser.php

namespace App\Services;

class RawParser
{
    private array $sceneStartPatterns = [
        '/^🚂.*Story$/',
        '/^💼\s*".+"/',
        '/^💔\s*".+"/',
        '/^##\s*Scene:/',
        '/^MALAM SEBELUMNYA$/',
        '/^PAGI\s*(—|HARI)?/',
        '/^\d{2}\.\d{2}\s*—/',
    ];

    private array $skipPatterns = [
        '/^Bagaimana tipikal/',
        '/^Coba skenarionya/',
        '/^Gimana infj/',
        '/^Ini seru banget/',
        '/^Kalau ngebayangin/',
        '/^Claude\'s response was interrupted/',
        '/^Feb \d+$/',
    ];

    public function parse(string $content): array
    {
        $lines = explode("\n", $content);
        $scenes = [];
        $currentScene = null;
        $inStoryMode = false;

        foreach ($lines as $i => $line) {
            $line = trim($line);

            if ($this->shouldSkip($line)) {
                continue;
            }

            if ($this->isSceneStart($line)) {
                if ($currentScene) {
                    $scenes[] = $currentScene;
                }
                $currentScene = [
                    'title' => $this->extractTitle($line),
                    'content' => '',
                    'line_start' => $i,
                ];
                $inStoryMode = true;
                continue;
            }

            if ($inStoryMode && $currentScene) {
                $currentScene['content'] .= $line . "\n";
            }
        }

        if ($currentScene) {
            $scenes[] = $currentScene;
        }

        return $scenes;
    }

    public function extractMetadata(string $content): array
    {
        $characters = [];
        if (preg_match('/INFJ:/', $content)) $characters[] = 'INFJ';
        if (preg_match('/INFP:/', $content)) $characters[] = 'INFP';

        return [
            'characters' => $characters,
            'mood' => $this->detectMood($content),
            'location' => $this->detectLocation($content),
            'tags' => $this->extractTags($content),
            'is_branch_point' => $this->detectBranchPoint($content),
        ];
    }

    private function detectMood(string $content): string
    {
        $moodKeywords = [
            'warm' => ['senyum', 'seneng', 'bahagia', 'sweet', 'hangat'],
            'tense' => ['deg-degan', 'nervous', 'tegang', 'panik'],
            'playful' => ['haha', 'hehe', 'lucu', 'nyebelin'],
            'sad' => ['sedih', 'nangis', 'mata merah', 'sesak'],
            'romantic' => ['sayang', 'cium', 'peluk', 'love'],
        ];

        $contentLower = strtolower($content);
        foreach ($moodKeywords as $mood => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($contentLower, $keyword)) {
                    return $mood;
                }
            }
        }

        return 'neutral';
    }

    // ... more helper methods
}
```

---

## 7. Features Breakdown

### Phase 1: Core (MVP)
- [ ] Project setup (Laravel 12 + Vue + Inertia)
- [ ] Auth (register, login, logout) - Laravel Breeze
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

## 8. Routes

### Web Routes (Inertia)
```php
// routes/web.php

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

    Route::resource('universes', UniverseController::class);
    Route::get('/universes/{universe}/import', [ImportController::class, 'show'])->name('universes.import');
    Route::post('/universes/{universe}/import', [ImportController::class, 'store']);

    Route::resource('universes.timelines', TimelineController::class)->shallow();
    Route::resource('timelines.scenes', SceneController::class)->shallow();
    Route::post('/scenes/{scene}/branch', [SceneController::class, 'branch'])->name('scenes.branch');

    Route::resource('universes.characters', CharacterController::class)->shallow();
    Route::resource('universes.tags', TagController::class)->shallow();
});
```

### API Routes
```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search', [SearchController::class, 'index']);
    Route::post('/scenes/{scene}/reorder', [SceneController::class, 'reorder']);
});
```

---

## 9. Environment Variables

```env
# .env.example

APP_NAME=StoryBranch
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=storybranch
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database
```

---

## 10. Next Steps

1. **Create Laravel project** - `composer create-project laravel/laravel amankila`
2. **Install Breeze with Vue + Inertia** - `php artisan breeze:install vue`
3. **Install additional packages** - TipTap, Vue Flow, Pinia, VueUse
4. **Setup database** - Create MySQL database, run migrations
5. **Build UI components** - Duolingo-style design system
6. **Build CRUD** - Universe, timeline, scene
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
