import m from "mithril"
import Lists from "../models/Lists"
import Project from "../models/Project"

let activeListId = 0;
let sortColumn = 'name'

const ListsTable = {
    view(vnode) {
        if (Lists.isLoading()) {
            return m('div', "Chargement…")
        }
        return m('table.table.table-striped.table-bordered.table-condensed',
            m('thead',
                m('tr',
                    m('th', {onclick() { sortColumn = 'name' }, style: "cursor: pointer;"},
                        Project.get().name + " - listes"),
                    m('th', {onclick() { sortColumn = 'date' }, style: "cursor: pointer;"},
                        "Dernière modification")
                ),
            ),
            m(ListBody),
        );
    },
}

const ListBody = {
    view() {
        const lists = Array.from(Lists.get()) // In-place sort must not change the list.
        if (!lists || lists.length === 0) {
            return m('tbody', 
                m('tr[colspan=2]', m('em', "Aucune liste n'est enregistrée sur le serveur."))
            );
        }
        if (sortColumn === 'date') {
            lists.sort((a, b) => (a.last_update < b.last_update ? 1 : -1))
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
        return m('tr', (l.id === activeListId ? {class: "info"} : {}),
            m('td',
                m(m.route.Link, {href: `/project/${l.project_id}/list/${l.id}`}, l.name)
            ),
            m('td', m(DateTime, {date: l.last_update})),
        );
    }
}

const DateTime = {
    format(date) {
        if (!(date instanceof Date)) {
            return ""
        }
        return date.toISOString().substring(0, 16).replace('T', ' ')
    },
    isRecent(date) {
        if (!(date instanceof Date)) {
            return null
        }
        const millis = Date.now() - date;
        return (millis < 86400000)
    },
    parseIsoDate(str) {
        // Date.parse() is browser dependent and its usage discouraged (source MDN).
        const m = str.split(/[^\d]/)
        if (!m || m.length < 6) {
            return null
        }
        return new Date(m[0], m[1] - 1, m[2], m[3], m[4], m[5])
    },
    view(vnode) {
        const ts = this.parseIsoDate(vnode.attrs.date)
        if (ts === null) {
            return null
        }
        return this.isRecent(ts) ? m('strong', this.format(ts)) : this.format(ts);
    },
}

const RefreshButton = {
    view(vnode) {
        return m('button.btn.btn-primary.btn-sm',
            {
                type: 'button',
                onclick: function() {
                    Lists.load(vnode.attrs.projectId);
                },
            },
            [m('i.fa.fa-refresh'), " Rafraîchir"]
        );
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
            m(ListsTable),
            m("div.actions",
                m(RefreshButton, {projectId: vnode.attrs.projectId}),
            ),
        );
    },
}
