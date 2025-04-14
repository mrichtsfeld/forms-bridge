import { useGeneral } from "../../../../src/providers/Settings";
import TemplateWizard from "../../../../src/components/Templates/Wizard";
import BridgeStep from "./BridgeStep";
import { useTemplateConfig } from "../../../../src/providers/Templates";

const apiFetch = wp.apiFetch;
const { useState, useEffect, useMemo, useRef } = wp.element;

const BREVO_HEADERS = ["api-key"];

const STEPS = [
  {
    name: "bridge",
    step: ({ fields, data, setData }) => (
      <BridgeStep fields={fields} data={data} setData={setData} />
    ),
    order: 20,
  },
];

function debounce(fn, ms = 500) {
  let timeout;

  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), ms);
  };
}

function validateBackendData(data) {
  return BREVO_HEADERS.reduce((isValid, field) => {
    return isValid && data.headers[field];
  }, true);
}

export default function BrevoTemplateWizard({ integration, onDone }) {
  const [{ backends }] = useGeneral();

  const config = useTemplateConfig();
  const configFields = config?.fields || [];
  const customFields = configFields
    .filter((field) => field.ref === "#bridge/custom_fields[]")
    .map((field) => field.name);

  const [data, setData] = useState({});

  const [lists, setLists] = useState([]);
  const [products, setProducts] = useState([]);
  const [pipelines, setPipelines] = useState([]);
  const [templates, setTemplates] = useState([]);

  const backendData = useMemo(() => {
    if (!data.backend?.name) return;
    const backend = backends.find(({ name }) => name === data.backend.name);

    if (backend && validateBackendData(backend)) {
      return backend;
    }

    const backendData = {
      name: data.backend.name,
      base_url: data.backend.base_url,
      headers: BREVO_HEADERS.reduce(
        (headers, name) => ({
          ...headers,
          [name]: data.backend[name],
        }),
        {}
      ),
    };

    if (validateBackendData(backendData)) {
      return backendData;
    }
  }, [data.backend, backends]);

  const fetchLists = useRef(
    debounce((data) => {
      const backend = {
        name: data.name,
        base_url: data.base_url,
        headers: BREVO_HEADERS.map((header) => ({
          name: header,
          value: data.headers[header],
        })),
      };

      apiFetch({
        path: "forms-bridge/v1/brevo/lists",
        method: "POST",
        data: backend,
      })
        .then(setLists)
        .catch(() => setLists([]));
    }, 500)
  ).current;

  const fetchPipelines = useRef(
    debounce((data) => {
      const backend = {
        name: data.name,
        base_url: data.base_url,
        headers: BREVO_HEADERS.map((header) => ({
          name: header,
          value: data.headers[header],
        })),
      };

      apiFetch({
        path: "forms-bridge/v1/brevo/pipelines",
        method: "POST",
        data: backend,
      })
        .then(setPipelines)
        .catch(() => setPipelines([]));
    }, 500)
  ).current;

  const fetchProducts = useRef(
    debounce((data) => {
      const backend = {
        name: data.name,
        base_url: data.base_url,
        headers: BREVO_HEADERS.map((header) => ({
          name: header,
          value: data.headers[header],
        })),
      };

      apiFetch({
        path: "forms-bridge/v1/brevo/products",
        method: "POST",
        data: backend,
      })
        .then(setProducts)
        .catch(() => setProducts([]));
    }, 500)
  ).current;

  const fetchTemplates = useRef(
    debounce((data) => {
      const backend = {
        name: data.name,
        base_url: data.base_url,
        headers: BREVO_HEADERS.map((header) => ({
          name: header,
          value: data.headers[header],
        })),
      };

      apiFetch({
        path: "forms-bridge/v1/brevo/templates",
        method: "POST",
        data: backend,
      })
        .then(setTemplates)
        .catch(() => setTemplates([]));
    }, 500)
  ).current;

  useEffect(() => {
    if (!backendData) return;

    (customFields.includes("listIds") ||
      customFields.includes("includeListIds")) &&
      fetchLists(backendData);
    (customFields.includes("product") || customFields.includes("products")) &&
      fetchProducts(backendData);
    customFields.includes("pipeline") && fetchPipelines(backendData);
    customFields.includes("templateId") && fetchTemplates(backendData);
  }, [backendData, config]);

  useEffect(
    () =>
      setData({
        ...data,
        bridge: {
          ...(data.bridge || {}),
          _lists: lists,
          _products: products,
          _pipelines: pipelines,
          _templates: templates,
        },
      }),
    [lists, products, pipelines]
  );

  return (
    <TemplateWizard
      integration={integration}
      data={data}
      setData={setData}
      onDone={onDone}
      steps={STEPS}
    />
  );
}
