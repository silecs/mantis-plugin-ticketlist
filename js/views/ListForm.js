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
                placeholder: "Pour les lignes commençant par un numéro de ticket, tous les tickets référencés dans le texte sont affichés.\n\nLe reste est vu comme un commentaire.",
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
            m('input.form-control', {
                oninput() {
                    List.setName(this.value)
                },
                placeholder: "nécessaire pour enregistrer la liste",
                value: List.get().name,}
            )
        );
    },
}

const SaveButton = {
    view(vnode) {
        const l = List.get()
        const creation = !(l.id > 0);
        return m('button.btn.btn-primary', {
            type: 'button',
            onclick() {
                List.save().then((result) => {
                    if (creation) {
                        const newList = result.content
                        m.route.set(`/project/${newList.projectId}/list/${newList.id}`)
                    }
                });
            },
            title: "Enregistrer cette liste sur le serveur",
            disabled: (l.name === '') || !List.hasChanged(),},
            [m('i.fa.fa-' + (creation ? 'plus' : 'pencil')), " Publier"]
        );
    },
}

const DeleteButton = {
    view(vnode) {
        const l = List.get()
        if (l.id === 0) {
            return null
        }
        return m('button.btn.btn-danger', {
            type: 'button',
            onclick() {
                if (!confirm("Supprimer définitivement cette liste du serveur ?")) {
                    return false
                }
                List.delete().then(() => m.route.set(`/project/${List.get().projectId}/list/new`))
            },
            title: "Supprimer cette liste du serveur",},
            [m('i.fa.fa-trash'), " Supprimer"]
        );
    },
}

export default {
    view(vnode) {
        return m('form.form#ticketlist-form',
            m(ListContent),
            m(ListName),
            m('div.actions',
                m(SaveButton),
                m(DeleteButton),
            ),
        );
    },
}
