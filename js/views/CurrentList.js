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
        .then(() =>
            Tickets.loadFromText(List.get().ids)
        )
    },
    view(vnode) {
        return m('div.blocks-container',
            m(WidgetBox, {class: "widget-color-blue2", id: "select-tickets", title: `Sélection`},
                m(ListForm),
            ),
            m(WidgetBox, {
                    title: `Tickets listés (${Tickets.get().length})`,
                    footer: m('div', 
                    "Temps total consacré à ces tickets : TODO",
                )},
                m(TicketsBlock, {
                    tickets: Tickets.get(),
                }),
            ),
            m(WidgetBox, {title: `Non validés`},
                m(TicketsBlock, {
                    tickets: Tickets.get().filter(t => t.status <= STATUS_RESOLVED),
                }),
            ),
            m(WidgetBox, {title: `Dev non fini`},
                m(TicketsBlock, {
                    tickets: Tickets.get().filter(t => t.status < STATUS_RESOLVED),
                }),
            ),
        );
    },
}
