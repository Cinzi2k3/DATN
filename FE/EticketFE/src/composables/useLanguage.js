import { useI18n } from "vue-i18n";

export function useLanguage() {
  const { locale, t } = useI18n();

  const changeLanguage = (lang) => {
    locale.value = lang;
  };

  return { locale, changeLanguage, t };
}