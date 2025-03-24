import { useWorkflowJob } from "../../providers/Workflows";
import WorkflowJobDetail from "./WorkflowJobDetail";

const {
  __experimentalItemGroup: ItemGroup,
  __experimentalItem: Item,
  SelectControl,
  Button,
} = wp.components;
const { useMemo } = wp.element;

export default function WorkflowPipeline({ workflow, setWorkflow, jobs }) {
  const [jobDetail, setJobDetail] = useWorkflowJob();

  const jobOptions = useMemo(
    () =>
      [{ label: "", value: "" }].concat(
        jobs.map((job) => ({
          value: job.name,
          label: job.title,
        }))
      ),
    [jobs]
  );

  const workflowJobs = useMemo(() => {
    if (!workflow.length) {
      return [{ name: "", title: "" }];
    }

    return workflow.map((name) => ({
      name,
      title: jobs.find((job) => job.name === name)?.title || name,
    }));
  }, [workflow, jobs]);

  const removeJob = (job) => {
    const index = workflow.findIndex((name) => name === job.name);

    if (index === -1) {
      return;
    }

    const newWorkflow = workflow
      .slice(0, index)
      .concat(workflow.slice(index + 1, workflow.length));

    setWorkflow(newWorkflow);
  };

  const appendJob = (job) => {
    let index = workflow.length;
    if (job) index = workflow.findIndex((name) => name === job.name);

    const newWorkflow = workflow
      .slice(0, index + 1)
      .concat([jobs[0].name])
      .concat(workflow.slice(index + 1, workflow.length));

    setWorkflow(newWorkflow);
  };

  const setJob = (jobName, index) => {
    const newWorkflow = workflow
      .slice(0, index)
      .concat([jobName])
      .concat(workflow.slice(index + 1, workflow.length));

    setWorkflow(newWorkflow);
  };

  if (jobDetail) {
    return <WorkflowJobDetail />;
  }

  return (
    <div style={{ width: "500px", maxWidth: "60vw" }}>
      <ItemGroup size="large">
        {workflowJobs.map((job, i) => (
          <Item size="large" isSepara>
            <div style={{ display: "flex", alignItems: "center" }}>
              <div style={{ padding: "0.25em 0.5em" }}>
                <strong>{i + 1}.&nbsp;</strong>
              </div>
              <div style={{ flex: 1, display: "block" }}>
                <SelectControl
                  value={job.name}
                  onChange={(jobName) => setJob(jobName, i)}
                  options={jobOptions}
                  __nextHasNoMarginBottom
                  __next40pxDefaultSize
                />
              </div>
              <div
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: "0.45em",
                  padding: "0 0.45em 0 0.75em",
                }}
              >
                <Button
                  size="compact"
                  variant="secondary"
                  onClick={() => appendJob(job)}
                >
                  +
                </Button>
                <Button
                  size="compact"
                  variant="secondary"
                  isDestructive
                  onClick={() => removeJob(job)}
                >
                  -
                </Button>
                <Button
                  size="compact"
                  variant="primary"
                  onClick={() => setJobDetail(job.name)}
                >
                  i
                </Button>
              </div>
            </div>
          </Item>
        ))}
      </ItemGroup>
    </div>
  );
}
