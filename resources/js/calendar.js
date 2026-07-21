import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('alpine:init', () => {
    Alpine.data('calendar', (wireComponent) => {
        let calendar = null;

        return {
            init() {
                const el = this.$el;
                const wire = wireComponent;

                calendar = new Calendar(el, {
                    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                    initialView: 'dayGridMonth',
                    firstDay: 1,
                    editable: true,
                    selectable: true,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay',
                    },
                    height: 'auto',
                    events: (info, successCallback) => {
                        wire.fetchEvents(info.startStr, info.endStr)
                            .then(events => successCallback(events))
                            .catch(() => successCallback([]));
                    },
                    eventDrop: (info) => {
                        if (! confirm('Mover evento para ' + info.event.start.toLocaleDateString() + '?')) {
                            info.revert();
                            return;
                        }
                        wire.moveEvent(info.event.id, info.event.startStr, info.event.endStr)
                            .then(result => { if (result?.error) info.revert(); })
                            .catch(() => info.revert());
                    },
                    eventResize: (info) => {
                        wire.moveEvent(info.event.id, info.event.startStr, info.event.endStr)
                            .then(result => { if (result?.error) info.revert(); })
                            .catch(() => info.revert());
                    },
                    eventClick: (info) => {
                        wire.openEditModal(info.event.id);
                    },
                    select: (info) => {
                        wire.openCreateModal(info.startStr, info.endStr);
                    },
                    eventDidMount: (info) => {
                        if (info.event.extendedProps.description) {
                            info.el.title = info.event.extendedProps.description;
                        }
                    },
                    locale: document.documentElement.lang || 'pt',
                });

                calendar.render();

                // Listen for external refresh
                Livewire.on('refresh-calendar', () => {
                    calendar.refetchEvents();
                });
            },

            destroy() {
                calendar?.destroy();
            },
        };
    });
});
