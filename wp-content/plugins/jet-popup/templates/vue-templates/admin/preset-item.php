<div :class="classList">
	<div class="jet-popup-library-page__item-inner">
        <div class="jet-popup-library-page__item-header">
            <div class="jet-popup-library-page__item-label"><span class="content-type-icon" v-if="contentTypeIcon" v-html="contentTypeIcon"></span><span class="preset-name">{{ title }}</span></div>
            <div class="jet-popup-library-page__item-actions">
                <div class="jet-popup-library-page__action-button">
                    <a slot="label" :href="permalink" target="_blank">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_7_106)">
                                <path d="M18.3 9.5C15 4.9 8.5 3.8 3.9 7.2C2.7 8.1 1.7 9.3 0.900002 10.6C1.1 11 1.4 11.4 1.7 11.8C5 16.4 11.3 17.4 15.9 14.2C16.8 13.5 17.6 12.8 18.3 11.8C18.6 11.4 18.8 11 19.1 10.6C18.8 10.2 18.6 9.8 18.3 9.5ZM10.1 7.2C10.6 6.7 11.4 6.7 11.9 7.2C12.4 7.7 12.4 8.5 11.9 9C11.4 9.5 10.6 9.5 10.1 9C9.6 8.5 9.6 7.7 10.1 7.2ZM10 14.9C6.9 14.9 4 13.3 2.3 10.7C3.5 9 5.1 7.8 7 7.2C6.3 8 6 8.9 6 9.9C6 12.1 7.7 14 10 14C12.2 14 14.1 12.3 14.1 10V9.9C14.1 8.9 13.7 7.9 13 7.2C14.9 7.8 16.5 9 17.7 10.7C16 13.3 13.1 14.9 10 14.9Z" fill="currentColor"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_7_106">
                                    <rect width="20" height="20" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </a>
                </div>
                <div class="jet-popup-library-page__action-button" @click="openModal">
            <span slot="label">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.6667 8H12.3333V3H7.33333V8H4L9.83333 13.8333L15.6667 8ZM4 15.5V17.1667H15.6667V15.5H4Z" fill="currentColor"></path></svg>
            </span>
                </div>
            </div>
        </div>

		<div class="jet-popup-library-page__item-thumb">
			<img :src="thumbUrl" alt="">
		</div>

		<div class="jet-popup-library-page__item-info">
			<div class="jet-popup-library-page__item-info-item jet-popup-library-page__item-category">
				<div class="category-info">
					<b><?php esc_html_e( 'Category:', 'jet-popup' ); ?></b>
					<span>{{categoryName}}</span>
				</div>
			</div>
			<div class="jet-popup-library-page__item-info-item jet-popup-library-page__item-install" v-if="install > 0">
				<div class="install-info">
					<b><?php esc_html_e( 'Installations: ', 'jet-popup' ); ?></b>
					<span style="{ display: block }">{{install}}</span>
				</div>
			</div>
			<div class="jet-popup-library-page__item-info-item jet-popup-library-page__item-required" v-if="requiredPlugins.length > 0">
				<b><?php esc_html_e( 'Required Plugins: ', 'jet-popup' ); ?></b>
				<div class="jet-popup-library-page__required-list">
					<div v-for="plugin in requiredPlugins" class="jet-popup-library-page__required-plugin">
						<a :href="plugin.link" target="_blank">
							<img :src="plugin.badge" alt="">
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
