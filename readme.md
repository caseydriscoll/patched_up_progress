# Patched Up Progress

A plugin for tracking work and stuff

Workflows are divided between tasks to be completed and the actions needed to complete those tasks.

Actions are the heart of the workflow, that is to say, the workflow is defined by the actions completed. The actions are a custom post type that gets published when the action is first started and gets and 'end_time' meta added when completed.

Actions are usually atomic, meaning the same action will be repeated many times over the course of a workflow. Example actions are 'Development', 'Eating' or 'Cleaning'. A single task may take many 'Development' actions to complete. Since WP Posts are expected to have unique slugs, each action slug gets a timestamp appended, to give it a unique representation.

Actions then are able to take on notes, media, taxonomy and other items as they are a normal custom post.

Tasks are stored as custom taxonomy, to mirror the Action custom post type. This reflects the one-to-many relationship between tasks and actions, while keeping it in a consitent WordPress form.

Tasks can be large or small, and can take one or many actions to complete. Thus, Tasks have hierarchy, where one Task can have many beneath is. A 'project' idea does not yet exist, as it is essentially a major task with several sub tasks.

Tasks are simply assigned to Actions, just like categories and tags.

Tasks can be on going, like 'Lunch', 'Workout' or 'Chores'. Since these Tasks are perpetual, they never end or close, they simply take in an unending amount of actions.

## Dictionary:

- Action: A custom post type, and the most atomic unit of work.
- Log:    A custom post type, an arbitrary list of weekly content
- Task:   A custom taxonomy, a collection of Actions

## Todo:

### Bugs
'Currently' time doesn't bind on ajax submit

### Settings
- Add tab for different plugins
- Tasks opens post admin menu not 'Progress' menu
- Set default settings
- Validate fields

### datetime
- Add stop time link in admin col
- Validate stop time to be G:i

### Polish and Refactoring
- Text domain or whatever
- Needs some serious js refactoring
- Does array assignment create another array? ['progress']['available_actions']
- better key listeners when vex and action fields are open

### Widgets
- Week view widget
- Slight dashboard visual tweaks
- Error check dashboard when on $instance available, maybe with 'dashboard' flag
- 'Pallet' idea for loadable color schemes
- Action colors based on context/cat
- Prevent overflow when action begins or ends beyond boundary
- Link to action

### Reports
- Add Reports page to menu
- Report tabs for day, week, month, year

### Actions

### Tasks
- Be able to 'complete' or 'archive' tasks <- through definition cpt meta
- Completely new Task UI

### Logs
- Add ability for bullets, indents and stuff. Maybe just all markdown
- Add option for type of tag for day name (strong now, maybe h4 for example)
- Time diff is off of timezone
- Start week on Sundays <- find sunday before birthday, count from then
- Shouldn't appear if not logged in

### Definition
- A 'definition' cpt to define taxonomies? Each tax, like a 'task', gets up to one 'definition' post to add more text, images and more too it. So you don't have to add stuff to tax itself
- Should probably just use posts2posts
