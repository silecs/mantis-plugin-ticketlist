import m from "mithril"
import Alerts from "./Alerts"
import ListsTable from "./ListsTable"
import CurrentList from "./CurrentList"
import Project from "../models/Project"

export default {
    oninit(vnode) {
        const projectId = parseInt(vnode.attrs.projectId, 10)
        Project.load(projectId)
    },
    onbeforeupdate(vnode, oldVnode) {
        if (oldVnode.attrs.projectId !== vnode.attrs.projectId) {
            const projectId = parseInt(vnode.attrs.projectId, 10)
            Project.load(projectId)
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
