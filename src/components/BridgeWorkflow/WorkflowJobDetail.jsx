import {
  useWorkflowJob,
  useWorkflowJobConfig,
} from "../../providers/Workflows";

const { __ } = wp.i18n;
const { Button } = wp.components;

export default function WorkflowJobDetail() {
  const [, setWorkflowJob] = useWorkflowJob();
  const detail = useWorkflowJobConfig();

  const close = () => setWorkflowJob(null);

  if (!detail) {
    return <p>Loading</p>;
  }

  const inputs = detail.input.length
    ? detail.input
    : [__("(not required fields)")];

  const outputs = detail.output.length
    ? detail.output
    : [__("(no payload mutations)")];

  return (
    <div style={{ display: "flex", width: "500px", maxWidth: "60vw" }}>
      <div style={{ padding: "1em 0.75em" }}>
        <Button variant="primary" size="compact" onClick={close}>
          &lt;
        </Button>
      </div>
      <div>
        <h2 style={{ textTransform: "uppercase" }}>{detail.title}</h2>
        <p>{detail.description}</p>
        <div style={{ display: "flex", gap: "0.5em" }}>
          <div style={{ flex: 1 }}>
            <h3>{__("Input fields", "forms-bridge")}</h3>
            <div style={{ border: "1px solid" }}>
              <ul style={{ listStyle: "disc", paddingLeft: "2em" }}>
                {inputs.map((field) => (
                  <li>
                    <p>
                      <strong>{field}</strong>
                    </p>
                  </li>
                ))}
              </ul>
            </div>
          </div>
          <div style={{ flex: 1 }}>
            <h3>{__("Output", "forms-bridge")}</h3>
            <div
              style={{
                color:
                  "var(--wp-components-color-accent,var(--wp-admin-theme-color,#3858e9))",
                border: "1px solid ",
              }}
            >
              <ul style={{ listStyle: "disc", paddingLeft: "2em" }}>
                {outputs.map((field) => (
                  <li>
                    <p>
                      <strong>{field}</strong>
                    </p>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
