import m from "mithril"
import Tickets from "../../models/Tickets";
import TicketsBlock from "./TicketsBlock";
import WidgetBox from "./WidgetBox";

const BlockTitle = {
    view(vnode) {
        const tickets = Tickets.get()
        return `Tickets listés (${tickets.length})`
    },
}

const TimeSpent = {
    view(vnode) {
        const timeSpent = Tickets.getTimeSpent()
        if (!timeSpent || !timeSpent.minutes) {
            return null
        }
        return m('div', 
            `Temps total consacré à ces tickets : ${timeSpent.time}`,
            timeSpent.minutes > 0 && timeSpent.release && timeSpent.release.name
                ? ["dont ", m('strong', timeSpent.timeSinceRelease), " depuis la livraison ", m('em', timeSpent.release.name)]
                : null
        );
    },
}

export default {
    view(vnode) {
        const tickets = Tickets.get()
        return m(WidgetBox, {
                class: 'tickets-block',
                title: m(BlockTitle),
                footer: m(TimeSpent),
            },
            m(TicketsBlock, {
                tickets: tickets,
            }),
        );
    },
}
