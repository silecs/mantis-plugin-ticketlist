import m from "mithril"
import List from "../models/List";
import Tickets from "../models/Tickets";

const FormGroup = {
    view(vnode) {
        return m('div.form-group',
            m('label.control-label', vnode.attrs.label),
            vnode.children
        );
    },
}

const ListContent = {
    view(vnode) {
        return m(FormGroup, {label: "Tickets #"},
            m('textarea.form-control', {
                cols:10,
                rows:20,
                placeholder: "un numéro par ligne",
                oninput(event) {
                    List.setText(this.value)
                    const ticketIds = List.getTicketIds()
                    if (Tickets.hasChanged(ticketIds)) {
                        Tickets.load(ticketIds, List.get().projectId)
                    }
                },},
                List.get().ids
            )
        );
    },
}

const ListName = {
    view(vnode) {
        return m(FormGroup, {label: "Titre de la liste"},
            m('input.form-control', {placeholder: "nécessaire pour enregistrer la liste", value: List.get().name})
        );
    },
}

export default {
    view(vnode) {
        return m('form.form',
            m(ListContent),
            m(ListName),
            m('button.btn.btn-primary', {title: "Enregistrer cette liste sur le serveur", disabled: (List.get().name === '')}, "Publier"),
        );
    },
}
