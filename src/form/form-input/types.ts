export interface FormInputOption {
	label: string;
	value: string;
}

export interface FormInputAttributes {
	type: string;
	variant: string;
	name: string;
	label: string;
	required: boolean;
	placeholder: string;
	hint: string;
	value: string;
	checked: boolean;
	options: FormInputOption[];
	mask: string;
	maskSourceName: string;
	citiesSourceName: string;
	[ key: string ]: unknown;
}
