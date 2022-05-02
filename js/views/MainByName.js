import m from "mithril"

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
            const issueIds = m.route.param('issueIds')
            if (!issueIds) {
                m.route.set(url)
            } else {
                m.route.set(url, {issueIds})
            }
        }).catch(function() {
            alert(`Erreur en lisant l'api GET /list`)
        })
    },
    view() {
        return null
    },
}
