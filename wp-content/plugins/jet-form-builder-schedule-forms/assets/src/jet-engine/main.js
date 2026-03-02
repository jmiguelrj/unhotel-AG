import {
	labels,
	help
} from "../source";

const JeScheduleForms = new Vue( {
	el: '#jet-schedule-forms',
	data: {
		schedule: {
			enable: false,
			from_date: '',
			to_date: '',
			pending_message: '',
			expired_message: '',
		},
	},
	created: function () {
		this.schedule = { ...this.schedule, ...JetScheduleForms.schedule };
	},
	methods: {
		label: function ( attr ) {
			return labels[ attr ];
		},
		help: function ( attr ) {
			return help[ attr ];
		},
		withPrefix: function ( suffix = '' ) {
			return `_jf_schedule_form${ suffix }`;
		},
		fieldName: function ( name ) {
			return this.withPrefix( `[${ name }]` );
		},
		uniqId: function ( name ) {
			return this.withPrefix( `__${ name }` );
		}
	},
} );