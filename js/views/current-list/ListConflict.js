import m from "mithril"
import ListConflict from "../../models/ListConflict"

const TicketsDiff = {
    view(vnode) {
        const before = vnode.attrs.before
        const after = vnode.attrs.after
        const all = new Set(before)
        for (const x of after) {
            if (!all.has(x)) {
                all.add(x)
            }
        }
        const result = []
        for(const x of all) {
            if (before.includes(x)) {
                if (after.includes(x)) {
                    result.push(m('span', {title: "Ticket présent sur le serveur et en local"}, x))
                } else {
                    result.push(m('span.removed', {title: "Ticket du serveur absent en local"}, x))
                }
            } else {
                result.push(m('span.added', {title: "Ticket local absent du serveur"}, x))
            }
        }
        return m('div.ticket-numbers', result)
    },
}

export default {
    view() {
        const conflict = ListConflict.get()
        if (!Object.hasOwn(conflict, 'serverList')) {
            return null
        }
        return m('div#list-conflict',
            m('div', "Liste enregistrée le : ", m('strong', conflict.serverList.lastUpdate)),
            m('h4', "Contenu"),
            m('pre', m('samp', conflict.serverList.ids)),
            m('h4', "Comparaison"),
            m(TicketsDiff, {before: conflict.serverIds, after: conflict.localIds}),
        )
    },
}
