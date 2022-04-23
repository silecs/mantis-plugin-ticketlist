import m from "mithril"
import List from "../models/List";
import Tickets from "../models/Tickets";
import ListForm from "./ListForm";
import TicketsBlock from "./TicketsBlock";
import WidgetBox from "./WidgetBox";

const STATUS_RESOLVED = 80

export default {
    oninit(vnode) {
        List.load(vnode.attrs.listId)
        .then(() => {
            const l = List.get()
            Tickets.loadFromText(l.ids, l.projectId)
        })
    },
    view(vnode) {
        const tickets = Tickets.get()
        const timeSpent = Tickets.getTimeSpent()
        return m('div.blocks-container',
            m(WidgetBox, {class: "widget-color-blue2", id: "select-tickets", title: `Sélection`},
                m(ListForm),
            ),
            m(WidgetBox, {
                    title: `Tickets listés (${tickets.length})`,
                    footer: m('div', 
                    `Temps total consacré à ces tickets : ${timeSpent.time}`,
                    timeSpent.minutes > 0 && timeSpent.release && timeSpent.release.name
                        ? ["dont ", m('strong', timeSpent.timeSinceRelease), " depuis la livraison ", m('em', timeSpent.release.name)]
                        : null
                )},
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
