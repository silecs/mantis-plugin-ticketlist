import m from "mithril"
import Alerts from "../models/Alerts"

const AlertBlock = {
    view(vnode) {
        return m('div.alert.alert-dismissible.alert-warning', {role: alert},
            m('button.close', {
                type: "button",
                "data-dismiss": "alert",
                "aria-label": "Close",
                onclick() {
                    Alerts.remove(vnode.attrs.message)
                }},
                "×"),
            vnode.attrs.message
        );
    },
}

export default {
    view() {
        return m("div#global-alerts",
            Alerts.get().map((message) => m(AlertBlock, {message}))
        );
    },
}
