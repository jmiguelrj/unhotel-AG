import {createControlComponentConfig, KoStandaloneControl} from '../control-base.js';

class AmeEvetButton extends KoStandaloneControl {
	public readonly eventName: string;
	public readonly eventData: unknown = null;
	public readonly wrap: boolean = false;

	constructor(params: any, $element: JQuery) {
		super(params, $element);

		if (typeof params['eventName'] === 'undefined') {
			throw new Error(
				'AmeEventButton requires an "eventName" parameter to be defined.'
			);
		}
		this.eventName = String(params['eventName']);

		if (typeof params['eventData'] !== 'undefined') {
			this.eventData = params['eventData'];
		}

		if (typeof params['wrap'] !== 'undefined') {
			this.wrap = Boolean(params['wrap']);
		}
	}

	triggerEvent(): void {
		this.findEventTarget()?.dispatchEvent(new CustomEvent(this.eventName, {
			detail: this.eventData,
			bubbles: true,
		}));
	}

	private cachedEventTarget: EventTarget | null = null;
	private triedToFindEventTarget: boolean = false;

	private findEventTarget(): EventTarget | null {
		if (this.triedToFindEventTarget) {
			return this.cachedEventTarget;
		}

		this.triedToFindEventTarget = true;
		const $child = this.findChild('input, p');
		if ($child.length === 0) {
			throw new Error(
				'AmeEventButton could not find its child element to dispatch the event on.'
			);
		}

		this.cachedEventTarget = $child[0];
		return this.cachedEventTarget;
	}

	get classes(): string[] {
		return ['button', 'ame-event-button-control', ...super.classes];
	}
}

export default createControlComponentConfig(AmeEvetButton, `
	<!-- ko if: wrap -->
	<p><input data-bind="class: classString, enable: isEnabled, click: triggerEvent, value: label" type="button"></p>
	<!-- /ko -->
	<!-- ko ifnot: wrap -->
	<input data-bind="class: classString, enable: isEnabled, click: triggerEvent, value: label" type="button">
	<!-- /ko -->
`);