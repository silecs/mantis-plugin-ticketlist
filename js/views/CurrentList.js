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
                ? m('i.fa.fa-exclamation-triangle', {title: "Les modifications locales ne sont pas encore enregistrées.", style: "color: #800000"})
                : null,
        );
    },
}

function update(projectId, listId) {
    List.setProjectId(projectId)
    if (listId > 0) {
        List.load(listId).then(() => {
            Tickets.load(List.getTicketIds(), List.get().projectId)
        })
    } else {
        List.reset()
        Tickets.load([], projectId)
    }
}

export default {
    oninit(vnode) {
        update(vnode.attrs.projectId, vnode.attrs.listId)
    },
    onbeforeupdate(vnode, oldVnode) {
        if (oldVnode.attrs.listId !== vnode.attrs.listId) {
            update(vnode.attrs.projectId, vnode.attrs.listId)
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
