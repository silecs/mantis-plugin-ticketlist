import m from "mithril"
import ListsTable from "./ListsTable"
import CurrentList from "./CurrentList"
import Project from "../models/Project"

export default {
    oninit(vnode) {
        const projectId = parseInt(vnode.attrs.projectId, 10)
        Project.load(projectId)
    },
    onbeforeupdate(oldVnode, newVnode) {
        if (oldVnode.attrs.projectId !== newVnode.attrs.projectId) {
            const projectId = parseInt(newVnode.attrs.projectId, 10)
            Project.load(projectId)
        }
    },
    view(vnode) {
        const listId = parseInt(vnode.attrs.listId, 10)
        const projectId = parseInt(vnode.attrs.projectId, 10)
        return m('div',
            m(ListsTable, {projectId}),
            m(CurrentList, {projectId, listId})
        )
    },
}
