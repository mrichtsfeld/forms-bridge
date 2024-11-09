// vendor
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { useState, useEffect } from "@wordpress/element";

export default function useFormFields({ formId }) {
  const [loading, setLoading] = useState();
  const [fields, setFields] = useState([]);

  useEffect(() => {
    apiFetch({
      path: `${window.wpApiSettings.root}wpct/v1/erp-forms/form/${formId}`,
      headers: {
        "X-WP-Nonce": wpApiSettings.nonce,
      },
    })
      .then((fields) => setFields(fields))
      .finally(() => setLoading(false));
  }, []);

  return { loading, fields };
}
