import {
	help,
	labels
} from "@/source";

const {
	ToggleControl,
	DateTimePicker,
	BaseControl,
	TextareaControl
} = wp.components;

const { __ } = wp.i18n;

const {
	useMetaState
} = JetFBHooks;

export default function PluginScheduleForm() {
	const [ scheduleOptions, setScheduleOptions ] = useMetaState( '_jf_schedule_form' );

	return <>
		<ToggleControl
			key={ 'calc_hidden' }
			label={ labels.enable }
			checked={ scheduleOptions.enable }
			onChange={ enable => {
				setScheduleOptions( prev => ( { ...prev, enable } ) );
			} }
		/>
		{ scheduleOptions.enable && <>
			<BaseControl label={ labels.from_date } className='jet-datepicker-control'>
				<DateTimePicker
					currentDate={ scheduleOptions.from_date }
					onChange={ from_date => {
						setScheduleOptions( prev => ( { ...prev, from_date } ) );
					} }
				/>
			</BaseControl>
			<BaseControl label={ labels.to_date } className='jet-datepicker-control'>
				<DateTimePicker
					currentDate={ scheduleOptions.to_date }
					onChange={ to_date => {
						setScheduleOptions( prev => ( { ...prev, to_date } ) );
					} }
				/>
			</BaseControl>
			<TextareaControl
				label={ labels.pending_message }
				help={ help.pending_message }
				value={ scheduleOptions.pending_message }
				onChange={ pending_message => {
					setScheduleOptions( prev => ( { ...prev, pending_message } ) );
				} }
			/>
			<TextareaControl
				label={ labels.expired_message }
				help={ help.expired_message }
				value={ scheduleOptions.expired_message  }
				onChange={ expired_message => {
					setScheduleOptions( prev => ( { ...prev, expired_message } ) );
				} }
			/>
		</> }
	</>;
}