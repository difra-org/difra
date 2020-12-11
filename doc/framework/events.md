# Difra Framwework Events

##How to working with event handlers

Get event object:  
`\Difra\Events\Event::getInstance(string $type)`  

Add event handler:  
`$event->registerHandler(callable $handler)`

Prevent default handlers call:  
`$event->preventDefault();`

Prevent any further handlers call, including default handlers:  
`$event->stopPropagation();`

##System events

Difra has some pre-defined events. They have default handlers and trigger automatically.

* Framework core events
  * EVENT_CORE_INIT:    Framework core initialization
  * EVENT_CONFIG_LOAD:  Configuration load
  * EVENT_PLUGIN_LOAD:  Plugins load
  * EVENT_PLUGIN_INIT:  Early plugins' hooks
* Web page generation events
  * EVENT_ACTION_REDEFINE:  Prepare for action processing
  * EVENT_ACTION_SEARCH:    Matching action search
  * EVENT_ACTION_DISPATCH:  Controller dispatch run
  * EVENT_ACTION_PRE_RUN:   Events processed before action run
  * EVENT_ACTION_RUN:       Action run events
  * EVENT_ACTION_ARRIVAL:   Events processed after action run
  * EVENT_ACTION_DONE:      Pre-render actions
  * EVENT_RENDER_INIT:      Render init events
  * EVENT_RENDER_RUN:       Render events
  * EVENT_RENDER_DONE:      After render events

##How to create a new event

Get event object:
`\Difra\Events\Event::getInstance(string $yourEventName);`

Register default handler:
`$event->registerDefaultHandler(callable $handler);`

Trigger event:
`$event->trigger();`