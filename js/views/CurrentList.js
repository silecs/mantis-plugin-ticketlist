import m from "mithril"
import List from "../models/List";
import Tickets from "../models/Tickets";
import ListForm from "./ListForm";
import TicketsBlock from "./TicketsBlock";
import WidgetBox from "./WidgetBox";

const STATUS_RESOLVED = 80

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

const Title = {
    view(vnode) {
        return m('span',
            "Sélection ",
            List.hasChanged()
                ? m('i.fa.fa-exclamation-triangle', {title: "Les modifications locales ne sont pas enregistrées.", style: "color: #800000"})
                : null,
        );
    },
}

export default {
    oninit(vnode) {
        List.setProjectId(vnode.attrs.projectId)
        if (vnode.attrs.listId > 0) {
            List.load(vnode.attrs.listId)
            .then(() => {
                Tickets.load(List.getTicketIds(), List.get().projectId)
            })
        }
    },
    view(vnode) {
        const tickets = Tickets.get()
        return m('div.blocks-container',
            m(WidgetBox, {class: "widget-color-blue2", id: "select-tickets", title: m(Title)},
                m(ListForm),
            ),
            m(WidgetBox, {
                    title: `Tickets listés (${tickets.length})`,
                    footer: m(TimeSpent)
                },
                m(TicketsBlock, {
                    tickets: tickets,
                }),
            ),
            m(WidgetBox, {title: `Non validés`},
                m(TicketsBlock, {
                    tickets: tickets.filter(t => t.status <= STATUS_RESOLVED),
                }),
            ),
            m(WidgetBox, {title: `Dev non fini`},
                m(TicketsBlock, {
                    tickets: tickets.filter(t => t.status < STATUS_RESOLVED),
                }),
            ),
        );
    },
}
