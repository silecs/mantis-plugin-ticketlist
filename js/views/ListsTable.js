import m from "mithril"
import List from "../models/List";
import Lists from "../models/Lists"
import Project from "../models/Project"
import Tickets from "../models/Tickets";

let activeListId = 0;

const ListsTable = {
    view(vnode) {
        if (Lists.isLoading()) {
            return m('div', "Chargement…")
        }
        return m('table.table.table-striped.table-bordered.table-condensed',
            m('thead',
                m('tr',
                    m('th', Project.get().name + " - listes"),
                    m('th', "Dernière modification")
                ),
            ),
            m(ListBody),
        );
    },
}

const ListBody = {
    view() {
        const lists = Lists.get()
        if (!lists || lists.length === 0) {
            return m('tbody', 
                m('tr[colspan=2]', m('em', "Aucune liste n'est enregistrée sur le serveur."))
            );
        }
        return m('tbody',
            lists.map(function (l) {
                return m(ListTr, {key: l.id, list: l})
            })
        )
    }
}

const ListTr = {
    view(vnode) {
        const l = vnode.attrs.list
        return m('tr', (l.id == activeListId ? {class: "info"} : {}),
            m('td',
                m(m.route.Link, {href: `/project/${l.project_id}/list/${l.id}`}, l.name)
            ),
            m('td', l.last_update),
        );
    }
}

const RefreshButton = {
    view(vnode) {
        return m('button.btn.btn-primary',
            {
                onclick: function() {
                    Lists.load(vnode.attrs.projectId);
                }
            },
            [m('i.fa.fa-refresh'), " Rafraîchir"]
        );
    },
}

const NewlistButton = {
    view(vnode) {
        if (!vnode.attrs.listId) {
            return null
        }
        return m('button.btn.btn-default',
            {
                onclick: function() {
                    m.route.set(`/project/${vnode.attrs.projectId}/list/new`)
                }
            },
            [m('i.fa.fa-eraser'), "Nouvelle liste"]
        )
    },
}

export default {
    oninit(vnode) {
        Project.load(vnode.attrs.projectId);
        // Load lists (of project vnode.attrs.projectId) through a GET request
        Lists.load(vnode.attrs.projectId);
    },
    view(vnode) {
        activeListId = vnode.attrs.listId
        return m('div#lists-table',
            m("div.actions", 
                m(RefreshButton, {projectId: vnode.attrs.projectId}),
                m(NewlistButton, {projectId: vnode.attrs.projectId, listId: activeListId}),
            ),
            m(ListsTable),
        );
    },
}
