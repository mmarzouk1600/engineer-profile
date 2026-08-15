import moment from 'moment';
import "moment/dist/locale/ar";

import getLodash from "lodash/get";
import eachRightLodash from "lodash/eachRight";
import replaceLodash from "lodash/replace";

export default {
    data() {
        return {}
    },
    mounted() {
        let locale = this.$page.props.locale === 'ar' ? 'en' : 'ar';
        moment.updateLocale(locale, {})
    },
    methods: {
        dateFormat(date = null, format = null, local = 'ar') {
            return moment(date).format(format ?? 'LLL');
            // .format(format ? format : 'H:m:s YYYY-M-DD');
        },
        hasOwnNestedProperty(obj, propertyPath) {
            if (!propertyPath)
                return false;

            var properties = propertyPath.split('.');

            for (var i = 0; i < properties.length; i++) {
                var prop = properties[i];

                if (!obj || !obj.hasOwnProperty(prop)) {
                    return false;
                } else {
                    obj = obj[prop];
                }
            }

            return true;
        },
        unique(collection, keyOrKeys) {
            let keys = [].concat(keyOrKeys)
            return collection.filter(
                (value, index, self) =>
                    self.findIndex(v => [...keys].every(k => v[k] === value[k])) === index
            )
        },
        getSequence(paginationObject, key) {
            return (paginationObject.current_page * paginationObject.per_page) + (key - paginationObject.per_page) + 1;
        },
        trans(string, args) {
            let value = getLodash(this.$page.props.language, string, replaceLodash(string, `${string.split('.')[0]}.`, ''));

            eachRightLodash(args, (paramVal, paramKey) => {
                value = replaceLodash(value, `:${paramKey}`, paramVal);
            });
            return value;
        }
    }
};
