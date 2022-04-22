import m from "mithril"
import ListsTable from "./ListsTable"
import CurrentList from "./CurrentList"
import Project from "../models/Project"

export default {
    onbeforeupdate(oldVnode, newVnode) {
        if (oldVnode.attrs.projectId !== newVnode.attrs.projectId) {
            Project.load(newVnode.attrs.projectId)
        }
    },
    view(vnode) {
        const listId = parseInt(vnode.attrs.listId, 10)
        const projectId = parseInt(vnode.attrs.projectId, 10)
        return m('div',
            m(ListsTable, {projectId}),
            m(CurrentList, {listId})
        )
    },
}
