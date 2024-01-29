import m from "mithril"
import {base64} from "../lib.js"

function appendIds(base, toappend) {
    if (!toappend || toappend.length === 0) {
        return base
    }
    const exists = new Set();
    for (const match of base.matchAll(/^(\d{3,5})(?: |$)/mg)) {
        exists.add(match[1])
    }
    for (const id of toappend.split(/,/)) {
        if (!exists.has(id)) {
            base += "\n" + id
        }
    }
    return base
}

export default {
    oninit(vnode) {
        m.request({
            method: "GET",
            url: `/plugin.php`,
            params: {
                page: "TicketList/api",
                action: "list",
                name: vnode.attrs.listName.replaceAll("+", " "),
                projectId: vnode.attrs.projectId,
            },
            withCredentials: true,
        }).then(function(l) {
            const url = `/project/${vnode.attrs.projectId}/list/${l.id}`
            const issueIds = appendIds(l.ids, m.route.param('issueIds'))
            console.log(issueIds)
            if (!issueIds) {
                m.route.set(url)
            } else {
                m.route.set(url, {issueIds: base64.encodeString(issueIds)})
            }
        }).catch(function() {
            alert(`Erreur en lisant l'api GET /list`)
        })
    },
    view() {
        return null
    },
}
