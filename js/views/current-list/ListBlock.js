import m from "mithril"
import List from "../../models/List";
import ListForm from "./ListForm";
import WidgetBox from "./WidgetBox";

const ListTitle = {
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
                type: 'button',
            },
            m('i.fa.fa-eraser')
        )
    },
}

export default {
    view(vnode) {
        return m(WidgetBox,
            {
                class: "widget-color-blue2",
                id: "select-tickets",
                title: m(ListTitle, {projectId: vnode.attrs.projectId, listId: vnode.attrs.listId}),
            },
            m(ListForm),
        );
    },
}
