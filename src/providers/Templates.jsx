// source
import useCurrentApi from "../hooks/useCurrentApi";
import { useApis } from "./Settings";

const apiFetch = wp.apiFetch;
const { createContext, useContext, useEffect, useState, useMemo } = wp.element;
const { __ } = wp.i18n;

const TemplatesContext = createContext({
  template: null,
  setTemplate: () => {},
  templates: [],
  config: null,
  submit: () => {},
});

export default function TemplatesProvider({ children }) {
  const api = useCurrentApi();
  const [apis] = useApis();
  const [template, setTemplate] = useState(null);
  const [config, setConfig] = useState(null);

  const templates = useMemo(() => {
    if (!api) return [];
    return apis[api]?.templates || [];
  }, [api, apis]);

  useEffect(() => {
    if (!template) {
      setConfig(null);
    } else {
      fetchConfig(template);
    }
  }, [template]);

  const fetchConfig = (template) => {
    if (!api) return;

    return apiFetch({
      path: `forms-bridge/v1/${api}/templates/${template}`,
    })
      .then(setConfig)
      .catch(() => {
        wpfb.emit("error", __("Loading config error", "forms-bridge"));
      });
  };

  const submit = ({ fields, integration }) => {
    if (!template || !api) {
      return;
    }

    wpfb.emit("loading", true);

    return apiFetch({
      path: `forms-bridge/v1/${api}/templates/${template}/use`,
      method: "POST",
      data: {
        integration,
        fields,
      },
    })
      .then(() => wpfb.emit("flushStore"))
      .catch(() => {
        wpfb.emit("error", __("Template submit error", "forms-bridge"));
      })
      .finally(() => wpfb.emit("loading", false));
  };

  return (
    <TemplatesContext.Provider
      value={{
        template,
        setTemplate,
        templates,
        config,
        submit,
      }}
    >
      {children}
    </TemplatesContext.Provider>
  );
}

export function useTemplate() {
  const { template, setTemplate } = useContext(TemplatesContext);
  return [template, setTemplate];
}

export function useTemplates() {
  const { templates } = useContext(TemplatesContext);
  return templates || [];
}

export function useTemplateConfig() {
  const { config } = useContext(TemplatesContext);
  return config;
}

export function useSubmitTemplate() {
  const { submit } = useContext(TemplatesContext);
  return (data) => submit(data);
}
