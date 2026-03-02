import { Input, Number, Select, SelectAsync, Sort, Toggle, CollapsibleContent, NestedRepeater } from 'modules/UI';
import { useListingData, useLocalizedData, useAsyncOptions } from "services/data";

const { postTypeOptions, postsOrderByOptions, postStatiOptions, taxonomiesOptions, taxonomyTermFieldOptions, termCompareOperatorOptions, metaCompareOperatorOptions, metaTypeOptions } = useLocalizedData();
const { loadPostOptions, loadUserOptions } = useAsyncOptions();

const QuerySettings = () => {
	// Data
	const {
		// selectors
		listingQueryProp: queryProp,
		// actions
		setListingQueryProp: updateQueryProp,
	} = useListingData();

	// Actions
	const updateRepeaterItem = (newValue, settingKey, item, repeaterKey) => {
		item[settingKey] = newValue;
		updateQueryProp(repeaterKey, queryProp(repeaterKey));
	};

	const clausesButtonLabel = (count) => {
		return count > 0 ? `Add OR Clauses` : 'Add Clauses';
	}

	return (
		<div className="jsf-listings-edit__content">
			<h2>Content</h2>
			<div className="jsf-control-posts-count">
				<Number
					label="Per Page"
					min='1'
					value={queryProp('posts_per_page')}
					onChange={(newValue) => updateQueryProp('posts_per_page', newValue)}
				/>
				<Number
					label="Offset"
					min='0'
					value={queryProp('offset')}
					onChange={(newValue) => updateQueryProp('offset', newValue)}
				/>
			</div>
			<div className="jsf-listings-edit__content__separator"></div>
			<h2>Order</h2>
			<Sort
				value={queryProp('sort')}
				options={postsOrderByOptions}
				onChange={(newValue) => updateQueryProp('sort', newValue)}
			/>
			<div className="jsf-listings-edit__content__separator"></div>
			<h2>Posts</h2>
			<Select
				label="Post Types"
				placeholder="Posts"
				value={queryProp('post_types')}
				options={postTypeOptions}
				isMulti={true}
				onChange={(newValue) => updateQueryProp('post_types', newValue)}
			/>
			<Select
				label="Post Status"
				placeholder="Published"
				value={queryProp('post_status')}
				options={postStatiOptions}
				isMulti={true}
				onChange={(newValue) => updateQueryProp('post_status', newValue)}
			/>
			<SelectAsync
				label="Post Authors"
				placeholder="Any"
				value={queryProp('post_authors')}
				loadOptions={loadUserOptions}
				isMulti={true}
				onChange={(newValue) => updateQueryProp('post_authors', newValue)}
			/>
			<SelectAsync
				label="Include Posts"
				placeholder="None"
				value={queryProp('post__in')}
				loadOptions={loadPostOptions}
				isMulti={true}
				onChange={(newValue) => updateQueryProp('post__in', newValue)}
			/>
			<SelectAsync
				label="Exclude Posts"
				placeholder="None"
				value={queryProp('post__not_in')}
				loadOptions={loadPostOptions}
				isMulti={true}
				onChange={(newValue) => updateQueryProp('post__not_in', newValue)}
			/>
			<Toggle
				label="Ignore Sticky Posts"
				value={queryProp('ignore_sticky_posts')}
				onChange={(newValue) => updateQueryProp('ignore_sticky_posts', newValue)}
			/>
			<CollapsibleContent
				closeLabel="Show advanced settings"
				openLabel="Hide advanced settings"
			>
				<h2>Taxonomies</h2>
				<p>Show content associated with certain taxonomies.</p>
				<NestedRepeater
					items={queryProp('taxonomies')}
					defaultItemData={{
						taxonomy: '',
						term_field: 'term_id',
						terms: '',
						operator: 'IN',
						child_terms: false,
						isOpen: true
					}}
					externalSeparator={<div className="jsf-repeater__separator__or">Or</div>}
					externalButtonAddLabel={ clausesButtonLabel( queryProp('taxonomies').length ) }
					internalLabelMask="Taxonomy:{taxonomy::_} {term_field}(s):{terms::_} operator:{operator}"
					internalSeparator={<div className="jsf-repeater__separator__and">And</div>}
					internalButtonAddLabel="Add AND Clause"
					onChange={(newValue) => updateQueryProp('taxonomies', newValue)}
				>
					{(item, index) =>
						<div className="jsf-taxonomies-repeater-item">
							<Select
								label="Taxonomy"
								description="Select taxonomy to get posts from"
								value={item.taxonomy}
								options={taxonomiesOptions}
								onChange={(newValue) => updateRepeaterItem(newValue, 'taxonomy', item, 'taxonomies')}
							/>
							<Select
								label="Field"
								description="Select taxonomy term by"
								value={item.term_field}
								options={taxonomyTermFieldOptions}
								onChange={(newValue) => updateRepeaterItem(newValue, 'term_field', item, 'taxonomies')}
							/>
							<Input
								label="Terms"
								description="Taxonomy term(s) to get posts by"
								value={item.terms}
								onChange={(newValue) => updateRepeaterItem(newValue, 'terms', item, 'custom_fields')}
							/>
							<Select
								label="Compare operator"
								description="Operator to test terms against"
								value={item.operator}
								options={termCompareOperatorOptions}
								onChange={(newValue) => updateRepeaterItem(newValue, 'operator', item, 'taxonomies')}
							/>
							<Toggle
								label="Child Terms"
								description="Include children for hierarchical taxonomies."
								value={item.child_terms}
								onChange={(newValue) => updateRepeaterItem(newValue, 'child_terms', item, 'taxonomies')}
							/>
						</div>
					}
				</NestedRepeater>
				<div className="jsf-listings-edit__content__separator"></div>
				<h2>Custom Fields</h2>
				<p>Show content associated with certain custom fields.</p>
				<NestedRepeater
					items={queryProp('custom_fields')}
					defaultItemData={{
						key: '',
						type: 'CHAR',
						compare: '=',
						value: '',
						isOpen: true
					}}
					externalSeparator={<div className="jsf-repeater__separator__or">Or</div>}
					externalButtonAddLabel={ clausesButtonLabel( queryProp('custom_fields').length ) }
					internalLabelMask="Field '{key::_}' {compare} {value::_}({type})"
					internalSeparator={<div className="jsf-repeater__separator__and">And</div>}
					internalButtonAddLabel="Add AND Clause"
					onChange={(newValue) => updateQueryProp('custom_fields', newValue)}
				>
					{(item, index) =>
						<div className="jsf-custom-fields-repeater-item">
							<div className="jsf-custom-fields-repeater-item__row">
								<Input
									label="Field Key"
									value={item.key}
									onChange={(newValue) => updateRepeaterItem(newValue, 'key', item, 'custom_fields')}
								/>
								<Select
									label="Field Type"
									value={item.type}
									options={metaTypeOptions}
									onChange={(newValue) => updateRepeaterItem(newValue, 'type', item, 'custom_fields')}
								/>
							</div>
							<div className="jsf-custom-fields-repeater-item__row">
								<Select
									label="Compare With"
									value={item.compare}
									options={metaCompareOperatorOptions}
									onChange={(newValue) => updateRepeaterItem(newValue, 'compare', item, 'custom_fields')}
								/>
								<Input
									label="Field Value"
									value={item.value}
									onChange={(newValue) => updateRepeaterItem(newValue, 'value', item, 'custom_fields')}
								/>
							</div>
						</div>
					}
				</NestedRepeater>
			</CollapsibleContent>
		</div>
	);
};

export default QuerySettings;