import m from "mithril"
import Alerts from "./Alerts"
import ListsTable from "./ListsTable"
import CurrentList from "./CurrentList"
import Project from "../models/Project"
import List from "../models/List"
import Tickets from "../models/Tickets"

function updateList(projectId, k) {
    List.setProjectId(projectId)

    const listId = parseInt(k, 10)
    if (isNaN(listId) || listId < 0) {
        List.reset()
        return Tickets.load([], projectId)
    }
    return List.load(listId).then(() => {
        Tickets.load(List.getTicketIds(), projectId)
    })
}

export default {
    oninit(vnode) {
        const projectId = parseInt(vnode.attrs.projectId, 10)
        Project.load(projectId)

        updateList(projectId, vnode.attrs.key).then(function() {
            const issueIds = m.route.param('issueIds') ?? ''
            if (issueIds !== '') {
                const issuesFiltered = issueIds.split(",")
                    .map(x => parseInt(x, 10))
                    .filter(x => !isNaN(x) && x > 0)
                    .join("\n")
                List.setText(issuesFiltered)
            }
        })
    },
    onbeforeupdate(vnode, oldVnode) {
        if (oldVnode.attrs.projectId !== vnode.attrs.projectId) {
            const projectId = parseInt(vnode.attrs.projectId, 10)
            Project.load(projectId)
        }
        if (oldVnode.attrs.key !== vnode.attrs.key) {
            updateList(vnode.attrs.projectId, vnode.attrs.key)
        }
    },
    view(vnode) {
        let projectId = parseInt(vnode.attrs.projectId, 10) ?? 0
        if (isNaN(projectId) || projectId < 0) {
            projectId = 0
        }
        let listId = parseInt(vnode.attrs.key, 10)
        if (isNaN(listId) || listId < 0) {
            listId = 0
        }
        return m('div',
            m(Alerts),
            m(ListsTable, {projectId, listId}),
            m(CurrentList, {projectId, listId})
        )
    },
}
