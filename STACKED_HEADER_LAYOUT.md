# Stacked Header Layout - Weekly Schedule

## Overview
Reorganized the weekly schedule header to display academic information (semester and academic year) below the main title, creating a cleaner two-line layout with better visual hierarchy.

## Visual Layout

### View Mode (Not Editing)
```
┌────────────────────────────────────────────────────────────┐
│ 🕐 Weekly Schedule  [⚠️ Setup Required]    [Edit Schedule] │
│          [📅 Second Semester]  [📚 AY 2025-2026]           │
└────────────────────────────────────────────────────────────┘
```

**Line 1:** Title + Setup badge (if needed) + Action button
**Line 2:** Semester badge + Academic year badge (indented)

### Edit Mode (Editing)
```
┌────────────────────────────────────────────────────────────┐
│ 🕐 Weekly Schedule              [💾 Save]  [✕ Cancel]      │
└────────────────────────────────────────────────────────────┘
```

**Line 1:** Title + Action buttons
**Line 2:** Hidden (clean edit mode)

## Design Benefits

### 1. Better Visual Hierarchy
- Title stands out more prominently
- Academic info grouped together on second line
- Clear parent-child relationship

### 2. Improved Readability
- Less horizontal crowding
- Information grouped by importance
- Easier to scan

### 3. Cleaner Layout
- More breathing room
- Better alignment
- Professional appearance

### 4. Flexible Design
- Accommodates longer text
- Scales better on smaller screens
- Room for future additions

## Implementation Details

### Structure
```jsx
<div className="flex justify-between items-start">
  <div>
    {/* Line 1: Title + Setup Badge */}
    <div className="flex items-center gap-3 mb-2">
      <svg>Clock Icon</svg>
      <h3>Weekly Schedule</h3>
      {!scheduleEditMode && !user?.schedule_initialized && (
        <span>Setup Required Badge</span>
      )}
    </div>
    
    {/* Line 2: Academic Info (only in view mode) */}
    {!scheduleEditMode && (
      <div className="flex items-center gap-3 ml-10">
        <span>Semester Badge</span>
        <span>Academic Year Badge</span>
      </div>
    )}
  </div>
  
  {/* Right side: Action buttons */}
  <div className="flex gap-3">
    {/* Edit/Save/Cancel buttons */}
  </div>
</div>
```

### Key CSS Classes

**Container:**
```css
flex justify-between items-start  /* Align to top, space between */
```

**Title Line:**
```css
flex items-center gap-3 mb-2  /* Horizontal layout, bottom margin */
```

**Academic Info Line:**
```css
flex items-center gap-3 ml-10  /* Horizontal layout, left indent */
```

**Indentation:**
- `ml-10` (40px) aligns badges with title text
- Creates visual hierarchy
- Shows relationship to title

## Spacing Details

### Vertical Spacing:
- Title to academic info: `mb-2` (8px)
- Comfortable reading distance
- Clear separation

### Horizontal Spacing:
- Between elements: `gap-3` (12px)
- Consistent throughout
- Balanced appearance

### Indentation:
- Academic info: `ml-10` (40px)
- Aligns with title text (after icon)
- Visual grouping

## Responsive Behavior

### Desktop (>1024px):
```
🕐 Weekly Schedule  [⚠️ Setup]              [Edit Schedule]
          [📅 Second Semester]  [📚 AY 2025-2026]
```

### Tablet (768px - 1024px):
```
🕐 Weekly Schedule  [⚠️ Setup]         [Edit]
          [📅 Second Semester]  [📚 AY 2025-2026]
```

### Mobile (<768px):
```
🕐 Weekly Schedule
[⚠️ Setup]
          [📅 Second Semester]
          [📚 AY 2025-2026]
                                    [Edit]
```

## Mode Comparison

### View Mode Features:
- Two-line layout
- Full academic context visible
- Setup badge on title line
- Edit button on right

### Edit Mode Features:
- Single-line layout
- Only title and actions visible
- Clean, focused interface
- Prominent Save/Cancel buttons

## Visual Hierarchy

### Priority Levels:

**Level 1 (Highest):**
- Weekly Schedule title
- Action buttons (Edit/Save/Cancel)

**Level 2 (Medium):**
- Setup Required badge (when shown)

**Level 3 (Context):**
- Semester badge
- Academic year badge

## Alignment Strategy

### Horizontal Alignment:
```
🕐 Weekly Schedule  [⚠️ Setup Required]    [Edit Schedule]
↓  ↓
│  └─ Title text starts here
└─ Icon
   
   [📅 Second Semester]  [📚 AY 2025-2026]
   ↑
   └─ Aligned with title text (ml-10)
```

### Vertical Alignment:
- Top line: `items-center` (vertically centered)
- Container: `items-start` (align to top)
- Allows for multi-line left content

## Color Scheme

### Title Line:
- Icon: `text-white`
- Title: `text-white font-bold text-2xl`
- Setup badge: `bg-amber-500 text-white`

### Academic Info Line:
- Badges: `bg-white/10 text-white`
- Icons: `text-green-200`
- Text: `font-semibold text-sm`

## Accessibility

### Screen Readers:
- Logical reading order (top to bottom)
- Title announced first
- Context follows naturally

### Visual Users:
- Clear hierarchy
- Easy to scan
- Grouped information

### Keyboard Navigation:
- Tab order maintained
- Focus visible
- Logical flow

## Benefits Summary

### User Experience:
✅ Clearer information hierarchy
✅ Less visual clutter
✅ Better readability
✅ More professional appearance

### Technical:
✅ Flexible layout
✅ Responsive friendly
✅ Easy to maintain
✅ Scalable design

### Design:
✅ Modern aesthetic
✅ Consistent spacing
✅ Balanced composition
✅ Clear visual flow

## Future Enhancements

Potential improvements:
- Semester dropdown for quick switching
- Academic year navigation
- Tooltip with full period dates
- Quick stats on hover
- Collapsible academic info
- Customizable display options

## Comparison with Previous Design

### Before (Inline):
```
🕐 Weekly Schedule  [📅 Second Semester]  [📚 AY 2025-2026]  [⚠️ Setup]  [Edit]
```
- Single line
- Crowded appearance
- Harder to scan

### After (Stacked):
```
🕐 Weekly Schedule  [⚠️ Setup Required]              [Edit Schedule]
          [📅 Second Semester]  [📚 AY 2025-2026]
```
- Two lines
- Better hierarchy
- Easier to read

## Implementation Notes

### Conditional Rendering:
- Academic info only shows in view mode
- Setup badge only shows when needed
- Clean edit mode maintained

### Alignment Technique:
- `ml-10` matches icon width + gap
- Consistent with title text position
- Visual grouping effect

### Spacing Strategy:
- `mb-2` between lines (8px)
- `gap-3` between elements (12px)
- Balanced and comfortable

## Summary

The stacked header layout provides:
- Better visual hierarchy with title prominence
- Cleaner organization of academic information
- Improved readability and scannability
- Professional, modern appearance
- Flexible design that scales well
- Clear distinction between view and edit modes

This design creates a more organized and user-friendly interface while maintaining all essential information in an easily accessible format.
