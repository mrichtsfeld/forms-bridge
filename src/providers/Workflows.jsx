// source
import { useApis } from "./Settings";

const apiFetch = wp.apiFetch;
const { createContext, useContext, useEffect, useState, useMemo, useRef } =
  wp.element;
const { __ } = wp.i18n;

const WorkflowsContext = createContext({
  job: null,
  setJob: () => {},
  jobs: [],
  config: null,
});

export default function WorflowsProvider({ children }) {
  const [apis] = useApis();

  const [api, setApi] = useState(null);
  const [job, setJob] = useState(null);
  const [config, setConfig] = useState(null);

  const jobs = useMemo(() => {
    if (!api) return [];
    return apis[api]?.workflow_jobs || [];
  }, [api, apis]);

  const onApi = useRef((api) => setApi(api)).current;

  useEffect(() => {
    wpfb.on("api", onApi);

    return () => {
      wpfb.off("api", onApi);
    };
  }, []);

  useEffect(() => {
    if (!job) {
      setConfig(null);
    } else {
      fetchConfig(job);
    }
  }, [job]);

  const fetchConfig = (job) => {
    return apiFetch({
      path: "forms-bridge/v1/workflow_jobs/" + job,
    })
      .then(setConfig)
      .catch(() => {
        wpfb.emit("error", __("Loading worflow job error", "forms-bridge"));
      });
  };

  return (
    <WorkflowsContext.Provider
      value={{
        job,
        setJob,
        jobs,
        config,
      }}
    >
      {children}
    </WorkflowsContext.Provider>
  );
}

export function useWorkflowJob() {
  const { job, setJob } = useContext(WorkflowsContext);
  return [job, setJob];
}

export function useWorkflowJobs() {
  const { jobs } = useContext(WorkflowsContext);
  return jobs || [];
}

export function useWorkflowJobConfig() {
  const { config } = useContext(WorkflowsContext);
  return config;
}
