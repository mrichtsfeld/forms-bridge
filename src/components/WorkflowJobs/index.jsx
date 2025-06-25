import WorkflowJobInterface from "../Workflow/StageInterface";

const { useState, useMemo, useEffect } = wp.element;
const apiFetch = wp.apiFetch;
const {
  PanelBody,
  SelectControl,
  Button,
  __experimentalSpacer: Spacer,
} = wp.components;
const { __ } = wp.i18n;

export default function WorkflowJobs({ api, jobs }) {
  const [job, setJob] = useState(null);
  const [data, setData] = useState(null);

  const jobOptions = useMemo(() => {
    return [{ name: "", value: null }].concat(
      jobs.map((job) => ({
        value: job.name,
        label: job.title,
      }))
    );
  }, [jobs]);

  const jobInput = useMemo(() => {
    if (!Array.isArray(data?.input)) return [];

    return data.input.map(({ name, schema, required }) => {
      return {
        name,
        schema,
        required,
        missing: false,
        mutated: false,
        optional: true,
      };
    });
  }, [data]);

  const loading = job && !data;

  const fetchData = (jobName) => {
    apiFetch({
      path: `forms-bridge/v1/${api}/workflow_jobs/${jobName}`,
      method: "GET",
    })
      .then(setData)
      .catch(() => {
        wpfb.emit(
          "error",
          __("Error while loading workflow job data", "forms-bridge")
        );
      });
  };

  useEffect(() => {
    if (!job) return;

    fetchData(job);

    return () => {
      setData(null);
    };
  }, [job]);

  return (
    <PanelBody title={__("Workflow jobs", "forms-bridge")} initialOpen={false}>
      <p>{__("Manage and edit addon workflow jobs", "forms-bridge")}</p>
      <div style={{ width: "300px" }}>
        <SelectControl
          value={job}
          onChange={setJob}
          options={jobOptions}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </div>
      <div>
        {(loading && <p>Loading...</p>) ||
          (data && (
            <>
              <Spacer paddingTop="calc(8px)" />
              <hr />
              <h2>{data.title}</h2>
              <p>{data.description}</p>
              <WorkflowJobInterface fields={jobInput} collapsible={false} />
              <Spacer paddingTop="calc(16px)" />
              <div style={{ display: "flex", gap: "0.5rem" }}>
                <Button variant="primary">Edit</Button>
                <Button variant="primary" isDestructive disabled={!job.custom}>
                  Reset
                </Button>
              </div>
            </>
          )) ||
          null}
      </div>
    </PanelBody>
  );
}
