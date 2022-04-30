import m from "mithril"
import List from "../../models/List";
import Tickets from "../../models/Tickets";
import TicketsTable from "./TicketsTable";
import WidgetBox from "./WidgetBox";

const BlockTitle = {
    view(vnode) {
        const tickets = Tickets.get()
        return [
            `Tickets listés (${tickets.length})`,
            m(RefreshButton, {num: tickets.length}),
        ];
    },
}

const RefreshButton = {
    loading: false,
    view(vnode) {
        if (vnode.attrs.num == 0) {
            return null
        }
        return m('button',
            {
                class: 'btn btn-default btn-sm',
                title: "Relire les états de ces tickets sur le serveur",
                type: 'button',
                onclick: () => {
                    this.loading = true
                    Tickets.load(List.getTicketIds(), List.get().projectId).then(() => {
                        this.loading = false
                    });
                },
                disabled: this.loading,
            },
            this.loading ? m('i.fa.fa-spinner') : m('i.fa.fa-refresh')
        )
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
            m(TicketsTable, {
                tickets: tickets,
            }),
        );
    },
}
