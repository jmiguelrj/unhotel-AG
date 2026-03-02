// Controls
import Input from "./controls/Input";
import Number from "./controls/Number";
import Select from "./controls/Select";
import SelectAsync from "./controls/SelectAsync";
import GroupedSelect from "./controls/GroupedSelect";
import Slider from "./controls/Slider";
import Sort from "./controls/Sort";
import Toggle from "./controls/Toggle";
import ToggleGroup from "./controls/ToggleGroup";
import ImagePicker from "./controls/ImagePicker";
import LoadingButton from "./controls/LoadingButton";

const controls = {
	Input,
	Number,
	Select,
	SelectAsync,
	GroupedSelect,
	Slider,
	Sort,
	Toggle,
	ToggleGroup,
	ImagePicker,
	LoadingButton
};

// Layout
import Repeater from "./layout/Repeater";
import NestedRepeater from "./layout/NestedRepeater";
import SortableRepeater from "./layout/SortableRepeater";
import CollapsibleContent from "./layout/CollapsibleContent";
const layout = {
	Repeater,
	NestedRepeater,
	SortableRepeater,
	CollapsibleContent
};

// SortableList
import SortableList from "./SortableList";

export {
	Input, Number, Select, SelectAsync, GroupedSelect, Slider, Sort, Toggle, ToggleGroup, ImagePicker, LoadingButton,
	Repeater, NestedRepeater, SortableRepeater, CollapsibleContent,
	SortableList,
};

export default {
	controls,
	layout,
	SortableList
};