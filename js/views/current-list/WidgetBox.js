import m from "mithril"

export default {
    view(vnode) {
        const title = vnode.attrs.title
        const footer = vnode.attrs.footer ?? null
        const attributes = {
            class: "widget-box block " + (vnode.attrs.class ?? ""),
        }
        if (vnode.attrs.id) {
            attributes.id = vnode.attrs.id
        }
        return m('section', attributes,
            m('div', {class: "widget-header widget-header-small"},
                m('h2', title)
            ),
            m('div', {class: "widget-body widget-main"},
                vnode.children
            ),
            footer ? m('div', {class: "widget-toolbox padding-8", style: "margin-top: 2em"}, footer) : null,
        );
    },
}
