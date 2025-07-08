import { EditorView, basicSetup } from "codemirror";
import { syntaxTree } from "@codemirror/language";
import { linter } from "@codemirror/lint";
import { php } from "@codemirror/lang-php";
import { useWorkflowJob } from "../../providers/Workflow";

const { useEffect, useRef } = wp.element;
const { Button } = wp.components;
const { __ } = wp.i18n;

export default function WorkflowEditor({ update, close }) {
  const workflowJob = useWorkflowJob();

  const docRef = useRef();
  const editorView = useRef();

  useEffect(() => {
    if (!workflowJob || !docRef.current) return;

    editorView.current = new EditorView({
      doc: workflowJob.snippet,
      parent: docRef.current,
      extensions: [basicSetup, php({ plain: true })],
      updateListener: console.log,
    });
  }, [workflowJob]);

  const save = () => {
    close();
  };

  if (!workflowJob) return;

  return (
    <>
      <p
        style={{
          marginTop: "-3rem",
          position: "absolute",
          zIndex: 1,
        }}
      >
        {__("Workflow job editor", "forms-bridge")}
      </p>
      <div
        style={{
          marginTop: "2rem",
          width: "1280px",
          maxWidth: "80vw",
          height: "500px",
          maxHeight: "80vh",
          display: "flex",
        }}
      >
        <div
          style={{
            flex: 1,
            maxWidth: "400px",
            display: "flex",
            flexDirection: "column",
            height: "100%",
            borderRight: "1px solid",
            paddingRight: "1em",
            marginRight: "1em",
          }}
        >
          <Button variant="primary" onClick={save}>
            {__("Save", "forms-bridge")}
          </Button>
          <Button variant="primary" isDestructive onClick={close}>
            {__("Discard", "forms-bridge")}
          </Button>
        </div>
        <div
          style={{
            flex: 2,
            display: "flex",
            flexDirection: "column",
            height: "100%",
          }}
        >
          <EditorWrapper id={workflowJob.id}>
            <div ref={docRef}></div>
          </EditorWrapper>
        </div>
      </div>
    </>
  );
}

function EditorWrapper({ id, children }) {
  const pre = `function forms_bridge_job_${id.replace(/-/g, "_")}($payload, $bridge)
{`;
  const post = `    return $payload;
}`;

  return (
    <>
      <code style={{ paddingLeft: "32px" }}>
        <pre
          style={{
            paddingLeft: "5px",
            margin: "2px 0",
            borderLeft: "1px solid #ddd",
            color: "#6c6c6c",
          }}
        >
          {pre}
        </pre>
      </code>
      {children}
      <code style={{ paddingLeft: "32px" }}>
        <pre
          style={{
            paddingLeft: "5px",
            margin: "2px 0",
            borderLeft: "1px solid #ddd",
            color: "#6c6c6c",
          }}
        >
          {post}
        </pre>
      </code>
    </>
  );
}
