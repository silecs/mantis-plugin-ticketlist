import m from "mithril"
import Alerts from "./Alerts"
import ListsTable from "./ListsTable"
import CurrentList from "./CurrentList"
import List from "../models/List"
import Project from "../models/Project"
import Tickets from "../models/Tickets"

export default {
    oninit(vnode) {
        const projectId = parseInt(vnode.attrs.projectId, 10)
        if (isNaN(projectId) || projectId < 0) {
            projectId = 0
        }
        Project.load(projectId)

        const ids = vnode.attrs.issueIds
            .split(",")
            .map(x => parseInt(x, 10))
            .filter(x => !isNaN(x) && x > 0)
        List.setText(ids.join("\n"))

        Tickets.load(ids, projectId)
    },
    view(vnode) {
        let projectId = parseInt(vnode.attrs.projectId, 10) ?? 0
        if (isNaN(projectId) || projectId < 0) {
            projectId = 0
        }
        return m('div',
            m(Alerts),
            m(ListsTable, {projectId, listId: 0}),
            m(CurrentList, {projectId, listId: 0})
        )
    }
}
