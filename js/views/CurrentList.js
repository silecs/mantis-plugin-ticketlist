import m from "mithril"

import ListConflict from "../models/ListConflict";
import Tickets from "../models/Tickets";

import ListBlock from "./current-list/ListBlock";
import ListConflictBlock from "./current-list/ListConflict";
import TicketsTable from "./current-list/TicketsTable";
import TicketsMainBlock from "./current-list/TicketsMainBlock";
import WidgetBox from "./current-list/WidgetBox";

const STATUS_RESOLVED = 80

export default {
    view(vnode) {
        const tickets = Tickets.get()
        return m(`div.blocks-container.ticket-count-${tickets.length}`,
            m(ListBlock, {
                projectId: vnode.attrs.projectId,
                listId: vnode.attrs.listId,
            }),
            ListConflict.isEmpty() ? null : m(WidgetBox, {title: "Conflit avec la version du serveur"},
                m(ListConflictBlock),
            ),
            m(TicketsMainBlock),
        );
    },
}
