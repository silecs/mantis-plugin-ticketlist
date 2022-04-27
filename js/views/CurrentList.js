import m from "mithril"
import List from "../models/List";
import ListConflict from "../models/ListConflict";
import Tickets from "../models/Tickets";
import ListConflictBlock from "./ListConflict";
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
        return [
            m('span',
                "Sélection ",
                List.hasChanged()
                    ? m('i.fa.fa-exclamation-triangle', {title: "Les modifications locales ne sont pas encore enregistrées.", style: "color: #800000"})
                    : null,
            ),
            m(LinkButton, {projectId: vnode.attrs.projectId, listId: vnode.attrs.listId}),
            m(NewlistButton, {projectId: vnode.attrs.projectId, listId: vnode.attrs.listId}),
        ];
    },
}

const LinkButton = {
    view(vnode) {
        if (vnode.attrs.listId) {
            return null
        }
        const issues = List.getTicketIds()
        if (issues.length < 2) {
            return null
        }
        return m(m.route.Link,
            {
                class: 'btn btn-default btn-sm',
                href: `/project/${vnode.attrs.projectId}/new/${issues.join(',')}`,
                title: "Lien pérenne vers cette liste (sans enregistrement sur le serveur)",
            },
            m('i.fa.fa-link')
        )
    },
}

const NewlistButton = {
    view(vnode) {
        if (!vnode.attrs.listId) {
            return null
        }
        return m('button.btn.btn-default.btn-sm',
            {
                onclick: function() {
                    m.route.set(`/project/${vnode.attrs.projectId}/list/new`)
                },
                title: "Nouvelle liste",
            },
            m('i.fa.fa-eraser')
        )
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
        return m(`div.blocks-container.ticket-count-${tickets.length}`,
            m(WidgetBox,
                {
                    class: "widget-color-blue2",
                    id: "select-tickets",
                    title: m(Title, {projectId: vnode.attrs.projectId, listId: vnode.attrs.listId}),
                },
                m(ListForm),
            ),
            ListConflict.isEmpty() ? null : m(WidgetBox, {title: "Conflit avec la version du serveur"},
                m(ListConflictBlock),
            ),
            m(WidgetBox, {
                    class: 'tickets-block',
                    title: `Tickets listés (${tickets.length})`,
                    footer: m(TimeSpent),
                },
                m(TicketsBlock, {
                    tickets: tickets,
                }),
            ),
            m(WidgetBox, {class: 'tickets-block', title: `Non validés`},
                m(TicketsBlock, {
                    tickets: tickets.filter(t => t.status <= STATUS_RESOLVED),
                }),
            ),
            m(WidgetBox, {class: 'tickets-block', title: `Dev non fini`},
                m(TicketsBlock, {
                    tickets: tickets.filter(t => t.status < STATUS_RESOLVED),
                }),
            ),
        );
    },
}
