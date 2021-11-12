namespace NND_Agent
{
    partial class NNDAgent
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.components = new System.ComponentModel.Container();
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(NNDAgent));
            this.NNDToolBarIcon = new System.Windows.Forms.NotifyIcon(this.components);
            this.NNDContext = new System.Windows.Forms.ContextMenuStrip(this.components);
            this.runSacnToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            this.NNDContext.SuspendLayout();
            this.SuspendLayout();
            // 
            // NNDToolBarIcon
            // 
            this.NNDToolBarIcon.BalloonTipIcon = System.Windows.Forms.ToolTipIcon.Info;
            this.NNDToolBarIcon.BalloonTipText = "Double Click To ";
            this.NNDToolBarIcon.ContextMenuStrip = this.NNDContext;
            this.NNDToolBarIcon.Icon = ((System.Drawing.Icon)(resources.GetObject("NNDToolBarIcon.Icon")));
            this.NNDToolBarIcon.Text = "NNDAgentIcon";
            this.NNDToolBarIcon.MouseDoubleClick += new System.Windows.Forms.MouseEventHandler(this.NNDToolBarIcon_MouseDoubleClick_1);
            // 
            // NNDContext
            // 
            this.NNDContext.ImageScalingSize = new System.Drawing.Size(20, 20);
            this.NNDContext.Items.AddRange(new System.Windows.Forms.ToolStripItem[] {
            this.runSacnToolStripMenuItem});
            this.NNDContext.Name = "contextMenuStrip1";
            this.NNDContext.Size = new System.Drawing.Size(139, 28);
            // 
            // runSacnToolStripMenuItem
            // 
            this.runSacnToolStripMenuItem.Name = "runSacnToolStripMenuItem";
            this.runSacnToolStripMenuItem.Size = new System.Drawing.Size(210, 24);
            this.runSacnToolStripMenuItem.Text = "Run Scan";
            this.runSacnToolStripMenuItem.Click += new System.EventHandler(this.RunScanToolStripMenuItem_Click);
            // 
            // NNDAgent
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(800, 450);
            this.Name = "NNDAgent";
            this.Text = "NND Agent";
            this.Shown += new System.EventHandler(this.NNDAgent_Shown);
            this.Resize += new System.EventHandler(this.NNDAgent_Resize);
            this.NNDContext.ResumeLayout(false);
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.NotifyIcon NNDToolBarIcon;
        private System.Windows.Forms.ContextMenuStrip NNDContext;
        private System.Windows.Forms.ToolStripMenuItem runSacnToolStripMenuItem;
    }
}

