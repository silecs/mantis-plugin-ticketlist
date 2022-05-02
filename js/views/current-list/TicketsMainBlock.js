import m from "mithril"
import List from "../../models/List";
import Project from "../../models/Project";
import Tickets from "../../models/Tickets";
import TicketsTable from "./TicketsTable";
import WidgetBox from "./WidgetBox";

let massCloseMode = false

const BlockTitle = {
    view(vnode) {
        const tickets = vnode.attrs.tickets
        return [
            `Tickets listés (${tickets.length})`,
            m('span.actions',
                m(RefreshButton, {num: tickets.length}),
                Project.hasManagerRights() ? m(ToggleMassCloseButton, {num: tickets.length}) : null,
            ),
        ];
    },
}

const RefreshButton = {
    loading: false,
    view(vnode) {
        if (vnode.attrs.num === 0) {
            return null
        }
        return m('button',
            {
                class: 'btn btn-default btn-sm',
                title: "Relire les états de ces tickets sur le serveur",
                type: 'button',
                onclick: () => {
                    this.loading = true
                    Tickets.load(List.getTicketIds(), List.get().projectId).then(() => {
                        this.loading = false
                    });
                },
                disabled: this.loading,
            },
            this.loading ? m('i.fa.fa-spinner') : m('i.fa.fa-refresh')
        )
    },
}

const ToggleMassCloseButton = {
    view(vnode) {
        if (vnode.attrs.num === 0) {
            // No ticket to select
            return null
        }
        return m('button',
            {
                class: 'btn btn-default btn-sm' + (massCloseMode ? ' active' : ''),
                title: "Activer le mode pour sélectionner des tickets et les fermer d'ensemble",
                type: 'button',
                onclick: () => {
                    massCloseMode = !massCloseMode
                },
            },
            m('i.fa.fa-wrench')
        )
    },
}

const MassCloseButton = {
    view(vnode) {
        if (!massCloseMode) {
            return null
        }
        const ids = vnode.attrs.selection
        return m('a',
            {
                class: 'btn btn-danger',
                title: "Ouvrir un formulaire pour fermer simultanément les tickets sélectionnés",
                href: '/plugin.php?page=TicketList/close&ids=' + ids.join(","),
                disabled: (ids.length === 0),
            },
            "Fermer…"
        )
    },
}

const TimeSpent = {
    view() {
        const timeSpent = Tickets.getTimeSpent()
        if (!timeSpent || !timeSpent.minutes) {
            return null
        }
        return m('div', 
            `Temps total consacré à ces tickets : ${timeSpent.time}`,
            timeSpent.minutes > 0 && timeSpent.release && timeSpent.release.name
                ? ["dont ", m('strong', timeSpent.timeSinceRelease), " depuis la livraison ", m('em', timeSpent.release.name)]
                : null
        );
    },
}

export default {
    selection: [],
    oninit() {
        this.selection = []
    },
    view() {
        const tickets = Tickets.get()
        return m(WidgetBox, {
                id: 'issues-main-table',
                class: 'tickets-block',
                title: m(BlockTitle, {tickets}),
                footer: m(TimeSpent),
            },
            [
                m(TicketsTable, {
                    tickets: tickets,
                    selectable: massCloseMode ? this.selection : null,
                }),
                m('div.actions', {style: "text-align: right"},
                    m(MassCloseButton, {selection: this.selection}),
                ),
            ]
        );
    },
}
