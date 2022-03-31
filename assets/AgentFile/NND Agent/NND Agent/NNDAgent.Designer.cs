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
            this.cancelScanToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            this.scanStatusToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            this.runSacnToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            this.closeApplicationToolStripMenuItem = new System.Windows.Forms.ToolStripMenuItem();
            this.txtIp = new System.Windows.Forms.TextBox();
            this.label1 = new System.Windows.Forms.Label();
            this.btnSubmitChange = new System.Windows.Forms.Button();
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
            this.cancelScanToolStripMenuItem,
            this.scanStatusToolStripMenuItem,
            this.runSacnToolStripMenuItem,
            this.closeApplicationToolStripMenuItem});
            this.NNDContext.Name = "contextMenuStrip1";
            this.NNDContext.Size = new System.Drawing.Size(196, 100);
            // 
            // cancelScanToolStripMenuItem
            // 
            this.cancelScanToolStripMenuItem.Name = "cancelScanToolStripMenuItem";
            this.cancelScanToolStripMenuItem.Size = new System.Drawing.Size(195, 24);
            this.cancelScanToolStripMenuItem.Text = "Cancel Scan";
            this.cancelScanToolStripMenuItem.Click += new System.EventHandler(this.CancelScanToolStripMenuItem_Click);
            // 
            // scanStatusToolStripMenuItem
            // 
            this.scanStatusToolStripMenuItem.Name = "scanStatusToolStripMenuItem";
            this.scanStatusToolStripMenuItem.Size = new System.Drawing.Size(195, 24);
            this.scanStatusToolStripMenuItem.Text = "Scan Status";
            this.scanStatusToolStripMenuItem.Click += new System.EventHandler(this.ScanStatusToolStripMenuItem_Click);
            // 
            // runSacnToolStripMenuItem
            // 
            this.runSacnToolStripMenuItem.Name = "runSacnToolStripMenuItem";
            this.runSacnToolStripMenuItem.Size = new System.Drawing.Size(195, 24);
            this.runSacnToolStripMenuItem.Text = "Run Scan";
            this.runSacnToolStripMenuItem.Click += new System.EventHandler(this.RunScanToolStripMenuItem_Click);
            // 
            // closeApplicationToolStripMenuItem
            // 
            this.closeApplicationToolStripMenuItem.Name = "closeApplicationToolStripMenuItem";
            this.closeApplicationToolStripMenuItem.Size = new System.Drawing.Size(195, 24);
            this.closeApplicationToolStripMenuItem.Text = "Close Application";
            this.closeApplicationToolStripMenuItem.Click += new System.EventHandler(this.CloseApplicationToolStripMenuItem_Click);
            // 
            // txtIp
            // 
            this.txtIp.Location = new System.Drawing.Point(15, 38);
            this.txtIp.Name = "txtIp";
            this.txtIp.Size = new System.Drawing.Size(135, 22);
            this.txtIp.TabIndex = 1;
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.Location = new System.Drawing.Point(12, 9);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(116, 16);
            this.label1.TabIndex = 2;
            this.label1.Text = "Manual IP Change";
            // 
            // btnSubmitChange
            // 
            this.btnSubmitChange.Location = new System.Drawing.Point(15, 67);
            this.btnSubmitChange.Name = "btnSubmitChange";
            this.btnSubmitChange.Size = new System.Drawing.Size(75, 23);
            this.btnSubmitChange.TabIndex = 3;
            this.btnSubmitChange.Text = "Submit";
            this.btnSubmitChange.UseVisualStyleBackColor = true;
            this.btnSubmitChange.Click += new System.EventHandler(this.btnSubmitChange_Click);
            // 
            // NNDAgent
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(800, 450);
            this.Controls.Add(this.btnSubmitChange);
            this.Controls.Add(this.label1);
            this.Controls.Add(this.txtIp);
            this.Name = "NNDAgent";
            this.Text = "NND Agent";
            this.FormClosing += new System.Windows.Forms.FormClosingEventHandler(this.NNDAgent_FormClosing);
            this.Load += new System.EventHandler(this.NNDAgent_Load);
            this.Shown += new System.EventHandler(this.NNDAgent_Shown);
            this.Resize += new System.EventHandler(this.NNDAgent_Resize);
            this.NNDContext.ResumeLayout(false);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.NotifyIcon NNDToolBarIcon;
        private System.Windows.Forms.ContextMenuStrip NNDContext;
        private System.Windows.Forms.ToolStripMenuItem runSacnToolStripMenuItem;
        private System.Windows.Forms.ToolStripMenuItem cancelScanToolStripMenuItem;
        private System.Windows.Forms.ToolStripMenuItem scanStatusToolStripMenuItem;
        private System.Windows.Forms.ToolStripMenuItem closeApplicationToolStripMenuItem;
        private System.Windows.Forms.TextBox txtIp;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.Button btnSubmitChange;
    }
}

